<?php

namespace App\Http\Services;

use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Models\Link;
use Exception;
use App\Models\User;
use App\Models\LinkHistory;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use Illuminate\Support\Facades\Hash;
use App\Models\AccountRequest;
use App\Http\Traits\LogsErrors;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Date;


class UserService implements UserServiceInterface
{
    use LogsErrors;
    /**
     * Get user data by user ID.
     * Retrieves user information from the database using the provided ID.
     *
     * @param int $id The ID of the user.
     * @return User|null The user object if found, or null if not found or an error occurs.
     */
    public function getUserData(int $id): ?User
    {
        try {
            $user = User::find($id);
            return $user ?? null;
        } catch (Exception $e) {
            $this->logError("Failed to fetch user data for ID", $e, ['id' => $id]);
            return null;
        }
    }

    /**
     * Update the user's profile information.
     * Updates the name and email of the user with the specified ID.
     * 
     * @param int $id The ID of the user.
     * @param string|null $name The new name of the user.
     * @param string $email The new email of the user.
     * @return bool|null Returns true if the update was successful, false if failed, or null if an error occurs.
     */
    public function updateProfile(int $id, ?string $name, string $email): ?bool
    {
        try {
            $result = User::where('id', $id)->update([
                'name' => $name,
                'email' => $email,
            ]);

            return $result ? true : false;
        } catch (Exception $e) {
            $this->logError("Failed to update profile for user ID", $e, [
                'user_id' => $id,
                'name' => $name,
                'email' => $email,
            ]);

            return null;
        }
    }


    /**
     * Update the user's password.
     * 
     * @param int $user_id The ID of the user.
     * @param string $new_password The new password to set.
     * @return bool|null Returns true if the update was successful, false if failed, or null if an error occurs.
     */
    public function updatePassword(int $user_id, string $new_password): ?bool
    {
        DB::beginTransaction();

        try {
            $user = User::find($user_id);
            if (!$user) {
                DB::rollBack();
                return false;
            }

            $user->password = Hash::make($new_password);
            $userUpdated = $user->save();

            if ($userUpdated) {
                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error updating password for user ID", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Create a user request if no active request exists for the user.
     * 
     * @param int $id The ID of the user.
     * @param string $type The type of the request.
     * @return bool|null Returns true if the request was successfully created, false if an active request exists, or null on error.
     */
    public function requestUserData(int $id, string $type): ?bool
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);

            $activeRequestsCount = $user->requests()
                ->where('expired', '>', now())
                ->count();

            if ($activeRequestsCount >= 2) {
                DB::commit();
                return false;
            }

            $newRequest = $user->requests()->create([
                'type' => $type,
                'status' => 'created',
                'expired' => now()->addDays(30),
            ]);

            if ($newRequest) {
                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Failed to create user request", $e, [
                'user_id' => $id,
                'type' => $type,
            ]);

            return null;
        }
    }

    /**
     * Retrieve a user by their email address.
     *
     * Performs a case-sensitive search for a user with the specified email.
     * Returns null if no user is found or if an error occurs.
     *
     * @param string $email The email address to search for
     * @return User|null The User model if found, null otherwise
     * @throws Exception On database query failure
     */
    public function getUserByEmail(string $email): ?User
    {
        try {
            $user = User::where('email', $email)->first();
            return (isset($user->id)) ? $user : null;
        } catch (Exception $e) {
            $this->logError("Error while retriving user data from email", $e);
            return null;
        }
    }

    /**
     * Retrieve top users by link count.
     *
     * Returns users ordered by the number of links they've created,
     * with an optional limit on the number of results.
     *
     * @param int|null $count Maximum number of users to return (default: 50)
     * @return iterable|null Collection of User models with links_count attribute,
     *                      or null on error
     * @throws Exception On database query failure
     */
    public function getTopUsers(?int $count = 50): ?iterable
    {
        try {
            return User::withCount('links')
                ->orderByDesc('links_count')
                ->limit($count)
                ->get();
        } catch (Exception $e) {
            $this->logError("Error while retrieving top users", $e);
            return null;
        }
    }

    /**
     * Update user profile and roles.
     *
     * Performs atomic update of user details and associated roles.
     * Rolls back all changes if any part of the operation fails.
     *
     * @param array $data {
     *     @var int    $id     User ID
     *     @var string $name   New user name
     *     @var string $email  New email address
     *     @var string $status New account status
     *     @var array  $roles  Array of role names to assign
     * }
     * @return bool|null True on success, false on failure, null on error
     * @throws Exception On database transaction failure
     */
    public function updateUser($data): ?bool
    {
        DB::beginTransaction();
        try {
            $user = User::find($data['id']);
            $result = $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $data['status'],
            ]);
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
            DB::commit();
            return $result !== null ? true : false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error update user data", $e);
            return null;
        }
    }

    /**
     * Permanently delete a user account.
     *
     * Performs a hard delete of the user record within a transaction.
     * All related data will be deleted via cascading constraints.
     *
     * @param int $id User ID to delete
     * @return bool|null True on success, null on error
     * @throws Exception On database transaction failure
     */
    public function destroyUser($id): ?bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error delete user data", $e);
            return null;
        }
    }

    /**
     * Retrieve complete user profile by ID.
     *
     * Fetches all user data including relations (if eager loaded).
     * Returns null if user not found or on error.
     *
     * @param int $id User ID to retrieve
     * @return User|null User model with all attributes, null if not found/error
     * @throws Exception On database query failure
     */
    public function getProfile(int $id): ?User
    {
        try {
            $user = User::where('id', $id)->first();
            return $user;
        } catch (Exception $e) {
            $this->logError("Error retrieving user profile", $e, ['user_id' => $id]);
            return null;
        }
    }


    /**
     * Freeze a user account and disable all their links.
     *
     * Atomic operation that:
     * 1. Marks all user's links as unavailable
     * 2. Updates user status to 'freezed'
     *
     * @param int $user_id ID of user to freeze
     * @return bool|null True on success, false if user not found, null on error
     * @throws Exception On database transaction failure
     */
    public function freezeAccount(int $user_id): ?bool
    {
        DB::beginTransaction();
        try {
            $user = User::find($user_id);
            //disable all links
            $user->links()->update([
                'available' => 0,
            ]);
            $result = $user->update([
                'status' => 'freezed',
            ]);
            DB::commit();
            if ($result)
                return $result ? true : false;
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Failed to freeze user account", $e, [
                'user_id' => $user_id,
            ]);
            return null;
        }
    }


    /**
     * Ban a user account and clean up related data.
     *
     * Performs atomic operation that:
     * 1. Deletes all link history records
     * 2. Disables all user's links
     * 3. Updates user status to 'banned'
     *
     * @param int $user_id ID of user to ban
     * @return bool|null True on success, false if user not found, null on error
     * @throws Exception On database transaction failure
     */
    public function banAccount(int $user_id): ?bool
    {
        DB::beginTransaction();
        try {
            $user = User::find($user_id);
            $linkIds = $user->links()->pluck('id');
            //delete all histories but not links
            LinkHistory::whereIn('link_id', $linkIds)->delete();
            //disable all links
            $user->links()->update([
                'available' => 0,
            ]);
            $result = $user->update([
                'status' => 'banned',
            ]);
            DB::commit();
            if ($result)
                return $result ? true : false;
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Failed to ban user account", $e, [
                'user_id' => $user_id,
            ]);
            return null;
        }
    }

    /**
     * Restore a previously banned/frozen account to active status.
     *
     * Note: Does not re-enable the user's links automatically.
     *
     * @param int $user_id ID of user to restore
     * @return bool|null True on success, false if user not found, null on error
     * @throws Exception On database transaction failure
     */
    public function unAccount(int $user_id): ?bool
    {
        DB::beginTransaction();
        try {
            $user = User::find($user_id);
            $result = $user->update([
                'status' => 'active',
            ]);
            DB::commit();
            if ($result)
                return $result ? true : false;
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Failed to un user account", $e, [
                'user_id' => $user_id,
            ]);
            return null;
        }
    }

    /**
     * Search users by name, email or IP address.
     *
     * Performs a comprehensive search that:
     * 1. First checks user names and emails
     * 2. Falls back to IP address search if no users found
     * 3. Can return either count or full results
     *
     * @param string $query Search term
     * @param bool|null $count If true, returns only count of matches
     * @return mixed Collection of users/ip stats if $count=false,
     *               integer count if $count=true,
     *               empty collection/0 on error
     * @throws Exception On database query failure
     */
    public function searchUsers(string $query, ?bool $count = false): mixed
    {
        try {
            $users = User::where('name', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->with([
                    'links' => function ($q) {
                        $q->select('id', 'user_id', 'ip_address');
                    }
                ])
                ->withCount('links')
                ->get();

            if ($users->isNotEmpty()) {
                return $count ? $users->count() : $users;
            }

            $ipStats = Link::where('ip_address', 'LIKE', '%' . $query . '%')
                ->select('ip_address')
                ->selectRaw('COUNT(*) as links_count')
                ->groupBy('ip_address')
                ->orderBy('links_count', 'DESC')
                ->get();

            return $count ? $ipStats->count() : $ipStats;

        } catch (Exception $e) {
            $this->logError("Error while searching users", $e);
            return $count ? 0 : collect();
        }
    }
}
