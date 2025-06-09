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

    public function getProfile(int $id): ?User
    {
        try {
            $user = User::where('id', $id)->first();
            return $user;
        } catch (Exception $e) {
            dd($e);
        }
    }


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
