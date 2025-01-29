<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Classes\Users;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SettingController extends Controller
{
    private $user_obj = null;

    /**
     * Constructor
     * 
     * @param Users $user_obj User service dependency.
     */
    public function __construct(Users $user_obj)
    {
        $this->middleware('role:user');
        $this->user_obj = $user_obj;
    }

    /**
     * Display the settings page.
     *
     * Fetches user data and passes it to the settings view.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse The settings view or an error response.
     */
    public function index()
    {
        try {

            $userData = $this->user_obj->getUserData(Auth::id());

            return view('user.settings')->with([
                'user_data' => $userData,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching user data for settings', $e, ['user_id' => Auth::id()]);
            abort(500);
        }
    }


    /**
     * Handle user data request.
     *
     * This method sends a user data request and provides feedback to the user.
     *
     * @return \Illuminate\Http\RedirectResponse A redirect response with success or error messages.
     */
    public function request_data()
    {
        try {

            $result =  $this->user_obj->requestUserData(Auth::id(), 'data');

            if ($result) {
                return redirect()->route('user.settings.index')->with('success', 'Link successfully has been sent.');
            } else {
                return redirect()->route('user.settings.index')->withErrors(['error' => 'Oops! Something went wrong and we couldn\'t send your request.']);
            }
        } catch (Exception $e) {
            $this->logError('Error processing user data request', $e, ['user_id' => Auth::id()]);
            return redirect()->route('user.settings.index')->withErrors([
                'error' => 'An unexpected error occurred while processing your request. Please try again later.',
            ]);
        }
    }


    /**
     * Handle user account deletion request.
     *
     * This method processes a user's account deletion request, logging them out and optionally
     * freezing their account for further actions.
     *
     * @return \Illuminate\Http\RedirectResponse A redirect response with success or error messages.
     */
    public function request_deletion(): \Illuminate\Http\RedirectResponse
    {
        try {

            $result = $this->user_obj->requestUserData(Auth::id(), 'deletion');

            if ($result) {
                $this->user_obj->freezeAccount(Auth::id());

                Auth::logout();

                return redirect()->route('login')->with([
                    'success' => 'Your account deletion request has been successfully submitted. You have been logged out.',
                ]);
            } else {
                return back()->withErrors([
                    'error' => 'Unable to process your deletion request at this time. Please try again later.',
                ]);
            }
        } catch (Exception $e) {
            $this->logError('Error processing account deletion request', $e, ['user_id' => Auth::id()]);

            return back()->withErrors([
                'error' => 'An unexpected error occurred while processing your request. Please try again later.',
            ]);
        }
    }
}
