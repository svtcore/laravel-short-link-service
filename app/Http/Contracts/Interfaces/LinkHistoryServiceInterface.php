<?php

namespace App\Http\Contracts\Interfaces;

use Illuminate\Support\Collection;

interface LinkHistoryServiceInterface
{
    /**
     * Get total clicks count for user's links
     *
     * @param  int  $user_id  User ID
     * @return int|null Click count or null
     */
    public function getTotalClicksByUserId(int $user_id): ?int;

    /**
     * Get total clicks count for specific link
     *
     * @param  int  $link_id  Link ID
     * @param  string|null  $startDate  Start date (Y-m-d)
     * @param  string|null  $endDate  End date (Y-m-d)
     * @return int|null Click count or null
     */
    public function getTotalClicksByLinkId(int $link_id, ?string $startDate, ?string $endDate): ?int;

    /**
     * Get unique IPs count for specific link
     *
     * @param  int  $link_id  Link ID
     * @param  string|null  $startDate  Start date (Y-m-d)
     * @param  string|null  $endDate  End date (Y-m-d)
     * @return int|null Unique IPs count or null
     */
    public function getUniqueIpsByLinkId(int $link_id, ?string $startDate, ?string $endDate): ?int;

    /**
     * Get unique IPs count for user's links
     *
     * @param  int  $user_id  User ID
     * @return int|null Unique IPs count or null
     */
    public function getUniqueIpsByUserId(int $user_id): ?int;

    /**
     * Get today's clicks count for user's links
     *
     * @param  int  $user_id  User ID
     * @return int|null Click count or null
     */
    public function getTodayTotalClicksByUserId(int $user_id): ?int;

    /**
     * Get top clicked links for user
     *
     * @param  int  $user_id  User ID
     * @return iterable|null Links collection or null
     */
    public function getTopLinksClicksByUserId(int $user_id): ?iterable;

    /**
     * Get top countries by unique clicks for user
     *
     * @param  int  $user_id  User ID
     * @return iterable|null Countries collection or null
     */
    public function getTopCountriesByUserId(int $user_id): ?iterable;

    /**
     * Get top browsers by unique clicks for user
     *
     * @param  int  $user_id  User ID
     * @return iterable|null Browsers collection or null
     */
    public function getTopBrowsersByUserId(int $user_id): ?iterable;

    /**
     * Get top OS by unique clicks for user
     *
     * @param  int  $user_id  User ID
     * @return iterable|null OS collection or null
     */
    public function getTopOperatingSystemsByUserId(int $user_id): ?iterable;

    /**
     * Get hourly clicks for user's links today
     *
     * @param  int  $user_id  User ID
     * @return array|null Hourly clicks array or null
     */
    public function getHourlyClicksByUserId(int $user_id): ?array;

    /**
     * Get hourly clicks for specific link
     *
     * @param  int  $link_id  Link ID
     * @param  string|null  $start_date  Start date (Y-m-d)
     * @param  string|null  $end_date  End date (Y-m-d)
     * @return array|null Hourly clicks array or null
     */
    public function getHourlyClicksByLinkId(int $link_id, ?string $start_date, ?string $end_date): ?array;

    /**
     * Get top metrics for link
     *
     * @param  int  $link_id  Link ID
     * @param  string|null  $start_date  Start date (Y-m-d)
     * @param  string|null  $end_date  End date (Y-m-d)
     * @param  string  $groupBy  Metric to group by
     * @return iterable|null Metrics collection or null
     */
    public function getTopMetricsByLinkId(int $link_id, ?string $start_date, ?string $end_date, string $groupBy): ?iterable;

    /**
     * Get daily clicks for link
     *
     * @param  int  $link_id  Link ID
     * @param  string|null  $start_date  Start date (Y-m-d)
     * @param  string|null  $end_date  End date (Y-m-d)
     * @return array|null Daily clicks array or null
     */
    public function getDailyClicksByLinkId(int $link_id, ?string $start_date, ?string $end_date): ?array;

    /**
     * Process redirect and track analytics
     *
     * @param  array  $data  Redirect data
     * @return array|null Destination URL or null
     */
    public function processRedirect(array $data): ?array;
}
