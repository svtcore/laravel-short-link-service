<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\Settings\MaintenanceModeRequest;

class SettingController extends Controller
{
    /**
     * @var UserServiceInterface $userService Users service instance
     */
    private $userService = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param UserServiceInterface $userService Users service instance
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->middleware('role:admin');
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.settings.index');
    }


    public function maintenanceMode(MaintenanceModeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            if ($validated['status']) {
                $secret = config('maintenance.secret');

                Artisan::call('down', [
                    '--secret' => $secret,
                    '--redirect' => config('maintenance.redirect', parse_url(route('admin.settings.index'), PHP_URL_PATH)),
                    '--retry' => config('maintenance.retry', 60),
                    '--status' => config('maintenance.status', 503),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Maintenance mode activated',
                    'secret' => $secret,
                ]);
            }

            Artisan::call('up');

            return response()->json([
                'success' => true,
                'message' => 'Maintenance mode deactivated',
            ]);
        } catch (Exception $e) {
            Log::error('Maintenance mode toggle failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'input' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update maintenance mode',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
