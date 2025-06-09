<?php

namespace App\Http\Contracts\Interfaces;

use Illuminate\Support\Collection;

interface LinkServiceInterface
{
    /**
     * Store a new short link in the database.
     *
     * @param string $url The destination URL
     * @param object $randomDomain The domain object
     * @param string $shortPath The generated short path
     * @param string|null $custom_name Optional custom name
     * @param int|null $user_id User ID or null for guests
     * @param string $ip IP address of creator
     * @return array|null Link data or null on failure
     */
    public function storeLink(string $url, object $randomDomain, string $shortPath, ?string $custom_name, ?int $user_id, string $ip): ?array;

    /**
     * Generate a short link for a given URL.
     *
     * @param string $url The destination URL
     * @param string|null $custom_name Optional custom name
     * @param int|null $user_id User ID or null for guests
     * @param string $ip IP address of creator
     * @return array|null Generated short link data or null
     */
    public function generateShortName(string $url, ?string $custom_name, ?int $user_id, string $ip): ?array;

    /**
     * Generate a secure random short URL path.
     *
     * @param int $length Length of path (default: 7)
     * @return string|null Generated path or null
     */
    public function generateShortPath(int $length = 7): ?string;

    /**
     * Get total number of links created by user.
     *
     * @param int $user_id User ID
     * @return int|null Link count or null
     */
    public function getTotalUserLinks(int $user_id): ?int;

    /**
     * Fetch detailed data for user's links.
     *
     * @param int|null $user_id User ID
     * @return Collection|null Link data collection or null
     */
    public function getUserLinksData(?int $user_id): ?Collection;

    /**
     * Check if link belongs to user.
     *
     * @param int|null $link_id Link ID
     * @param int|null $user_id User ID
     * @return bool True if belongs, false otherwise
     */
    public function isOwnUser(?int $link_id, ?int $user_id): bool;

    /**
     * Retrieve link by ID.
     *
     * @param string $id Link ID
     * @return object|null Link object or null
     */
    public function getById(string $id): ?object;

    /**
     * Update link details.
     *
     * @param string|null $name Custom name
     * @param string $destination Destination URL
     * @param bool $availability Availability status
     * @param int $id Link ID
     * @return int|null Affected rows count or null
     */
    public function updateLink(?string $name, string $destination, bool $availability, int $id): ?int;

    /**
     * Delete link and its histories.
     *
     * @param int $id Link ID
     * @return bool|null True if success, null on error
     */
    public function destroyLink(int $id): ?bool;

    /**
     * Get paginated list of links.
     *
     * @return iterable|null Links collection or null
     */
    public function getLinksList(): ?iterable;

    /**
     * Search links by query.
     *
     * @param string $query Search query
     * @param bool $count Return count only
     * @return mixed Search results
     */
    public function searchLinks(string $query, bool $count): mixed;

    /**
     * Search links by domain ID.
     *
     * @param int $id Domain ID
     * @return iterable|null Links collection or null
     */
    public function searchByDomainId(int $id): ?iterable;

    /**
     * Search links by creator IP.
     *
     * @param string $ip IP address
     * @return iterable|null Links collection or null
     */
    public function searchByUserIP(string $ip): ?iterable;
}
