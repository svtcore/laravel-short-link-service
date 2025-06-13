<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SettingController extends Controller
{
    use \App\Http\Traits\LogsErrors;
    /**
     * @var UserServiceInterface $usersService Users service instance
     */
    private $usersService = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param UserServiceInterface $usersService Users service instance
     */
    public function __construct(UserServiceInterface $usersService)
    {
        $this->middleware('role:user');
        $this->usersService = $usersService;
    }

    /**
     * Display user settings page
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse Returns:
     * - Settings view with user data if successful
     * - 500 error page if fails
     *
     * @throws \Exception Logs errors and aborts on failure
     */
    public function index()
    {
        try {
            $userData = $this->usersService->getUserData(Auth::id());

            if (!$userData) {
                throw new Exception('Authenticated user not found');
            }

            return view('user.settings')->with([
                'user_data' => $userData,
            ]);

        } catch (Exception $e) {
            $this->logError('Error fetching user data for settings', $e, ['user_id' => Auth::id()]);
            abort(500);
        }
    }


    /**
     * Request user data export
     *
     * @return \Illuminate\Http\RedirectResponse Returns:
     * - Redirect to settings with success message if successful
     * - Redirect with error message if fails
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function requestData()
    {
        try {

            $result = $this->usersService->requestUserData(Auth::id(), 'data');

            if (!$result) {
                throw new Exception('Query failed');
            }

            return redirect()
                ->route('user.settings.index')
                ->with('success', 'Your request has been sent!');
        } catch (Exception $e) {
            $this->logError('Error processing user data request', $e, ['user_id' => Auth::id()]);
            return redirect()->route('user.settings.index')->withErrors([
                'error' => 'An unexpected error occurred while processing your request. Please try again later.',
            ]);
        }
    }


    /**
     * Request account deletion
     *
     * @return \Illuminate\Http\RedirectResponse Returns:
     * - Redirect to home with success message if successful
     * - Redirect back with error message if fails
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function requestDeletion(): \Illuminate\Http\RedirectResponse
    {
        try {
            $id =  Auth::id();
            $result = $this->usersService->requestUserData($id, 'deletion');
            if ($result) {
                $this->usersService->freezeAccount($id);
                Auth::logout();

                return redirect()->route('home')->with(
                    'success',
                    'Your account deletion request has been submitted. You have been logged out.'
                );
            }

            return back()->withErrors([
                'error' => 'Unable to process your deletion request. Please try again later.'
            ]);

        } catch (Exception $e) {
            $this->logError('Account deletion error', $e, ['user_id' => Auth::id()]);
            return back()->withErrors([
                'error' => 'A system error occurred.'
            ]);
        }
    }
}
