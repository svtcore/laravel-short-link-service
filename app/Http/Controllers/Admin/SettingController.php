<?php

namespace App\Http\Controllers\Admin;

use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\MaintenanceModeRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * @var UserServiceInterface Users service instance
     */
    private $userService = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param  UserServiceInterface  $userService  Users service instance
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->middleware('role:admin');
        $this->userService = $userService;
    }

    /**
     * Display admin settings page
     *
     * @return \Illuminate\View\View Returns settings view
     */
    public function index(): View
    {
        return view('admin.settings.index');
    }

    /**
     * Toggle maintenance mode status
     *
     * @param  MaintenanceModeRequest  $request  Validated request containing:
     *                                           - status: Boolean indicating whether to activate maintenance mode
     * @return \Illuminate\Http\JsonResponse Returns JSON with:
     *                                       - success: Boolean indicating operation status
     *                                       - message: Status message
     *                                       - secret: Maintenance mode secret (when activating)
     *
     * @throws \Exception Logs errors and returns error response
     */
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
