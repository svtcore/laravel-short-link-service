<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Traits\LogsErrors;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Http\Requests\Admin\Users\UpdateRequest;
use App\Http\Requests\Admin\Users\DestroyRequest;
use App\Http\Requests\Admin\Users\ShowRequest;
use App\Http\Requests\Admin\Users\SetStatusRequest;
use App\Http\Requests\Admin\Users\UpdatePasswordRequest;
use App\Http\Requests\Admin\Users\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    use LogsErrors;

    /**
     * @var UserServiceInterface $userService Users service instance
     */
    private $userService = null;

    /**
     * @var LinkServiceInterface $serviceLink Links service instance
     */
    private $serviceLink = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param UserServiceInterface $userService Users service instance
     * @param LinkServiceInterface $serviceLink Links service instance
     */
    public function __construct(
        UserServiceInterface $userService,
        LinkServiceInterface $serviceLink
    ) {
        $this->middleware('role:admin');
        $this->userService = $userService;
        $this->serviceLink = $serviceLink;
    }

    /**
     * Display a listing of top users.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        try {
            $users = $this->userService->getTopUsers(null) ?? [];
        } catch (Exception $e) {
            $users = [];
            $this->logError('Failed to fetch top users', $e);
        }

        return view('admin.users.index')->with([
            'users' => $users,
        ]);
    }


    public function show(ShowRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $user_data = $this->userService->getProfile($validatedData['id']);
            $links = $this->serviceLink->getUserLinksData($validatedData['id']) ?? [];

            return view('admin.users.show')->with(['user' => $user_data, 'links' => $links]);
        } catch (Exception $e) {
            $this->logError('Error in show method', $e);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the user']);
        }
    }


    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->updateUser($validatedData);

            return $result
                ? redirect()->back()->with('success', 'User successfully updated')
                : redirect()->back()->withErrors(['error' => 'An error occurred while updating the user']);
        } catch (Exception $e) {
            $this->logError('Error in update method', $e, ['id' => $id]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the user']);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyRequest $request, string $id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->destroyUser($validatedData['id']);

            if ($result) {
                return redirect()->route('admin.users.index')->with('success', 'User data and related links, histories successfully deleted');
            } else {
                return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the user']);
            }
        } catch (Exception $e) {
            $this->logError('Error deleting user', $e, ['user_id' => $id, 'auth_user' => auth()->id()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the user']);
        }
    }


    public function ban(SetStatusRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        return $this->processUserStatusChange(
            'banAccount',
            $validatedData['id'],
            'User has been banned, related links were disabled',
            'An error occurred while banning the user'
        );
    }

    public function active(SetStatusRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        return $this->processUserStatusChange(
            'unAccount',
            $validatedData['id'],
            'User has been unbanned or unfrozen successfully',
            'An error occurred while unbanning/unfreezing the user'
        );
    }

    public function freeze(SetStatusRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        return $this->processUserStatusChange(
            'freezeAccount',
            $validatedData['id'],
            'User has been frozen, related links were disabled',
            'An error occurred while freezing the user'
        );
    }

    /**
     * Private helper method to handle status changes
     */
    private function processUserStatusChange(string $method, int $userId, string $successMessage, string $errorMessage): RedirectResponse
    {
        try {
            $result = $this->userService->$method($userId);
            if ($result) {
                return redirect()->back()->with('success', $successMessage);
            }
            return redirect()->back()->withErrors(['message' => $errorMessage]);
        } catch (Exception $e) {
            $this->logError($errorMessage, $e, ['user_id' => $userId, 'admin_id' => auth()->id()]);
            return redirect()->back()->withErrors(['message' => $errorMessage]);
        }
    }



    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->updateProfile(
                Auth::id(),
                $validatedData['name'],
                $validatedData['email']
            );

            if ($result) {
                return back()->with('success', 'Profile has been successfully updated.');
            } else {
                return back()->withErrors(['message' => 'Failed to update profile data. Please try again.']);
            }
        } catch (Exception $e) {
            $this->logError("An unexpected error occurred while updating your profile", $e, ['user_id' => Auth::id()]);
            return redirect()->route('admin.settings.index')->withErrors(['message' => 'An unexpected error occurred while updating your profile']);
        }
    }


    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->userService->updatePassword(Auth::id(), $validatedData['new_password']);

            if ($result) {
                return back()->with('success', 'Password successfully updated.');
            } else {
                return back()->withErrors(['message' => 'Failed to update password. Please try again.']);
            }
        } catch (Exception $e) {
            $this->logError("An error occurred while updating the password", $e, ['user_id' => Auth::id()]);
            return back()->withErrors(['message' => 'An unexpected error occurred while updating the password']);
        }
    }
}
