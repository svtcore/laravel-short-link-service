<?php

namespace App\Http\Classes;

use App\Models\Link;
use Exception;
use App\Models\User;
use App\Models\LinkHistory;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use Illuminate\Support\Facades\Hash;
use App\Models\AccountRequest;
use App\Http\Traits\LogsErrors;

class Users
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
            return $user;
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
            $hasActiveRequest = $user->requests()
                ->where('expired', '>', now())
                ->exists();

            if ($hasActiveRequest) {
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

    public function freezeAccount(int $user_id): ?bool
    {
        DB::beginTransaction();
        try {
            $result = User::where('id', $user_id)->update([
                'status' => 'freezed',
            ]);
            DB::commit();
            if ($result) return $result ? true : false;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Failed to freeze user account", $e, [
                'user_id' => $user_id,
            ]);
        }
    }

    public function getUserByEmail(string $email): ?User
    {
        try{
            $user = User::where('email', $email)->first();
            return (isset($user->id)) ? $user : null; 
        }
        catch(Exception $e){
            $this->logError("Error while retriving user data from email", $e);
            return null;
        }
    }
}
