<?php

namespace App\Http\Controllers\User;

use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UpdatePasswordRequest;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Http\Traits\LogsErrors;
use Exception;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use LogsErrors;

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
        $this->middleware('role:user');
        $this->userService = $userService;
    }

    /**
     * Update user profile information
     *
     * @param  UpdateProfileRequest  $request  Contains validated:
     *                                         - name: New user name
     *                                         - email: New user email
     * @return \Illuminate\Http\RedirectResponse Returns:
     *                                           - Redirect to settings with success message if successful
     *                                           - Redirect with error message if fails
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->updateProfile(
                Auth::id(),
                $validatedData['name'],
                $validatedData['email']
            );

            if (! $result) {
                throw new Exception('Profile update service returned false');
            }

            return redirect()
                ->route('user.settings.index')
                ->with('success', 'Profile updated successfully!');

        } catch (Exception $e) {
            $this->logError('An unexpected error occurred while updating your profile', $e, ['user_id' => Auth::id()]);

            return redirect()->route('user.settings.index')->withErrors(['error' => 'An unexpected error occurred while updating your profile']);
        }
    }

    /**
     * Update user password
     *
     * @param  UpdatePasswordRequest  $request  Contains validated:
     *                                          - current_password: Current password for verification
     *                                          - new_password: New password to set
     * @return \Illuminate\Http\RedirectResponse Returns:
     *                                           - Redirect to settings with success message if successful
     *                                           - Redirect back with error message if fails
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->updatePassword(Auth::id(), $validatedData['new_password']);

            if (! $result) {
                throw new Exception('Password update service returned false');
            }

            return redirect()
                ->route('user.settings.index')
                ->with('success', 'Password successfully updated!');

        } catch (Exception $e) {
            $this->logError('An error occurred while updating the password', $e, ['user_id' => Auth::id()]);

            return back()->withErrors(['error' => 'An unexpected error occurred while updating the password']);
        }
    }
}
