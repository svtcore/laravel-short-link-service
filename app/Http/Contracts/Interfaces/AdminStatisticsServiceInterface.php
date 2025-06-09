<?php

namespace App\Http\Contracts\Interfaces;

interface AdminStatisticsServiceInterface
{
    /**
     * Get total links count
     *
     * @param bool|null $available Filter by availability status
     * @return int Link count
     */
    public function getTotalLinks(bool $available = null): int;

    /**
     * Get total active links count
     *
     * @return int Active links count
     */
    public function getTotalActiveLinks(): int;

    /**
     * Get total users count
     *
     * @param string|null $startDate Start date filter (Y-m-d)
     * @param string|null $endDate End date filter (Y-m-d)
     * @return int Users count
     */
    public function getTotalUsers(?string $startDate = null, ?string $endDate = null): int;

    /**
     * Get total clicks count
     *
     * @return int Clicks count
     */
    public function getTotalClicks(): int;

    /**
     * Get total unique clicks count
     *
     * @param string|null $startDate Start date filter (Y-m-d)
     * @param string|null $endDate End date filter (Y-m-d)
     * @return int Unique clicks count
     */
    public function getTotalUniqueClicks(?string $startDate = null, ?string $endDate = null): int;

    /**
     * Get average clicks per link
     *
     * @return int Average clicks per link
     */
    public function getAvgClicksPerLink(): int;

    /**
     * Get links count by date range
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return int Links count
     */
    public function getTotalLinksByDate(string $startDate, string $endDate): int;

    /**
     * Get clicks count by date range
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return int Clicks count
     */
    public function getTotalClicksByDate(string $startDate, string $endDate): int;

    /**
     * Get daily clicks breakdown by date range
     *
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array|null Date => clicks pairs
     */
    public function getDailyClicksByDate(?string $startDate, ?string $endDate): ?array;

    /**
     * Get hourly clicks breakdown by date range
     *
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array|null Hour => clicks pairs (0-23)
     */
    public function getHourlyClicksByDate(?string $startDate, ?string $endDate): ?array;

    /**
     * Get top countries by unique clicks
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return iterable|null Top countries with click counts
     */
    public function getTopCountriesByDate(string $startDate, string $endDate): ?iterable;

    /**
     * Get top browsers by unique clicks
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return iterable|null Top browsers with click counts
     */
    public function getTopBrowsersByDate(string $startDate, string $endDate): ?iterable;

    /**
     * Get top operating systems by unique clicks
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return iterable|null Top OS with click counts
     */
    public function getTopOSByDate(string $startDate, string $endDate): ?iterable;
}
