<?php

namespace App\Http\Contracts\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserServiceInterface
{
    /**
     * Get user data by ID
     *
     * @param  int  $id  User ID
     * @return User|null User object or null
     */
    public function getUserData(int $id): ?User;

    /**
     * Update user profile
     *
     * @param  int  $id  User ID
     * @param  string|null  $name  New name
     * @param  string  $email  New email
     * @return bool|null True if success, false if failed, null on error
     */
    public function updateProfile(int $id, ?string $name, string $email): ?bool;

    /**
     * Update user password
     *
     * @param  int  $user_id  User ID
     * @param  string  $new_password  New password
     * @return bool|null True if success, false if failed, null on error
     */
    public function updatePassword(int $user_id, string $new_password): ?bool;

    /**
     * Create user request
     *
     * @param  int  $id  User ID
     * @param  string  $type  Request type
     * @return bool|null True if created, false if exists, null on error
     */
    public function requestUserData(int $id, string $type): ?bool;

    /**
     * Get user by email
     *
     * @param  string  $email  User email
     * @return User|null User object or null
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Get top users by links count
     *
     * @param  int|null  $count  Limit (default: 50)
     * @return iterable|null Users collection or null
     */
    public function getTopUsers(?int $count = 50): ?iterable;

    /**
     * Update user data
     *
     * @param  array  $data  User data
     * @return bool|null True if success, null on error
     */
    public function updateUser($data): ?bool;

    /**
     * Delete user
     *
     * @param  int  $id  User ID
     * @return bool|null True if success, null on error
     */
    public function destroyUser($id): ?bool;

    /**
     * Get user profile
     *
     * @param  int  $id  User ID
     * @return User|null User object or null
     */
    public function getProfile(int $id): ?User;

    /**
     * Freeze user account
     *
     * @param  int  $user_id  User ID
     * @return bool|null True if success, null on error
     */
    public function freezeAccount(int $user_id): ?bool;

    /**
     * Ban user account
     *
     * @param  int  $user_id  User ID
     * @return bool|null True if success, null on error
     */
    public function banAccount(int $user_id): ?bool;

    /**
     * Unban user account
     *
     * @param  int  $user_id  User ID
     * @return bool|null True if success, null on error
     */
    public function unAccount(int $user_id): ?bool;

    /**
     * Search users
     *
     * @param  string  $query  Search query
     * @param  bool|null  $count  Return count only
     * @return mixed Search results
     */
    public function searchUsers(string $query, ?bool $count = false): mixed;
}
