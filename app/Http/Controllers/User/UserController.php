<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Classes\Users;
use App\Http\Requests\User\Profile\UpdatePasswordRequest;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\LogsErrors;

class UserController extends Controller
{
    use LogsErrors;

    private $user_obj = null;

    public function __construct(Users $user_obj)
    {
        $this->middleware('role:user');
        $this->user_obj = $user_obj;
    }

    /**
     * Update the specified resource in storage.
     *
     * This method is responsible for updating the user's profile information.
     * It validates the input data, updates the profile in the database, and
     * returns an appropriate response to the user.
     *
     * @param \App\Http\Requests\UpdateProfileRequest $request The request containing the validated user input.
     *
     * @return \Illuminate\Http\RedirectResponse A redirect response with a success or error message.
     */
    public function update_profile(UpdateProfileRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $result = $this->user_obj->updateProfile(
                Auth::id(),
                $validatedData['name'],
                $validatedData['email']
            );

            if ($result) {
                return redirect()->route('user.settings.index')->with('success', 'Profile data has been successfully updated');
            } else {
                return redirect()->route('user.settings.index')->withErrors(['error' => 'Oops! Profile data has not been updated']);
            }
        } catch (Exception $e) {
            $this->logError("An unexpected error occurred while updating your profile", $e, ['user_id' => Auth::id()]);
            return redirect()->route('user.settings.index')->withErrors(['error' => 'An unexpected error occurred while updating your profile']);
        }
    }

    /**
 * Update the user's password in storage.
 *
 * This method is responsible for updating the user's password.
 * It validates the current and new password, and attempts to
 * update the password in the database. If the update is successful,
 * it returns a success message, otherwise an error message.
 *
 * @param \Illuminate\Http\Request $request The request containing the current and new password data.
 *
 * @return \Illuminate\Http\RedirectResponse A redirect response with a success or error message.
 */
public function update_password(UpdatePasswordRequest $request)
{
    try {
        $validatedData = $request->validated();

        $result = $this->user_obj->updatePassword(Auth::id(), $validatedData['new_password']);

        if ($result) {
            return back()->with(['success' => 'Password successfully updated!']);
        } else {
            return back()->withErrors(['error' => 'Oops! Password has not been updated']);
        }
    } catch (Exception $e) {
        $this->logError("An error occurred while updating the password", $e, ['user_id' => Auth::id()]);
        return back()->withErrors(['error' => 'An unexpected error occurred while updating the password']);
    }
}

}
