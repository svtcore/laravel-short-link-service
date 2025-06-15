<?php

namespace App\Http\Services;

use App\Http\Contracts\Interfaces\AdminStatisticsServiceInterface;
use App\Http\Traits\LogsErrors;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AdminStatisticsService implements AdminStatisticsServiceInterface
{
    use LogsErrors;

    private $maxDaysLimit = 365;

    private function parseAndValidateDateRange(?string $startDate, ?string $endDate): array
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        if ($startDate && $endDate && $startDate->diffInDays($endDate) > $this->maxDaysLimit) {
            $endDate = $startDate->copy()->addDays($this->maxDaysLimit);
        }

        return [$startDate, $endDate];
    }

    /**
     * Get the total number of links created in the system.
     *
     * This method retrieves the total count of links stored in the database.
     * It ensures that if an error occurs during the database query, the method
     * gracefully handles it and returns 0.
     *
     * @return int The total count of links, or 0 if an error occurs.
     */
    public function getTotalLinks(?bool $available = null): int
    {
        try {
            $query = Link::query();

            if (! is_null($available)) {
                $query->where('available', $available);
            }

            $count = $query->count();

            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError('Error while retrieving total links', $e);

            return 0;
        }
    }

    /**
     * Get the total number of active links in the system.
     *
     * This method retrieves the count of active links (where `available` is true).
     * If an error occurs or the count is invalid
     *
     * @return int The total count of active links, or 0 if an error occurs.
     */
    public function getTotalActiveLinks(): int
    {
        try {
            $activeLinksCount = Link::where('available', true)->count();

            return ($activeLinksCount >= 1) ? $activeLinksCount : 0;
        } catch (Exception $e) {
            $this->logError('Error while retrieving total active links', $e);

            return 0;
        }
    }

    /**
     * Get total number of users in the system
     *
     * @param  string|null  $startDate  Optional start date filter (format: Y-m-d)
     * @param  string|null  $endDate  Optional end date filter (format: Y-m-d)
     * @return int Returns:
     *             - Total user count (filtered by date range if provided)
     *             - 0 if error occurs
     *
     * @throws \Exception Logs errors and returns 0 on failure
     */
    public function getTotalUsers(?string $startDate = null, ?string $endDate = null): int
    {
        try {

            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $query = User::query();
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            $count = $query->count();

            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError('Error while retrieving users count', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 0;
        }
    }

    /**
     * Get total number of clicks in the system
     *
     * @return int Returns:
     *             - Total click count
     *             - 0 if error occurs
     *
     * @throws \Exception Logs errors and returns 0 on failure
     */
    public function getTotalClicks(): int
    {
        try {
            return LinkHistory::count();
        } catch (Exception $e) {
            $this->logError('Error while retrieving click count', $e);

            return 0;
        }
    }

    /**
     * Get the total number of unique clicks based on IP address and link.
     *
     * This method counts the unique IP addresses that have clicked on different
     * links, ensuring that if the same IP address clicks on different links, it is
     * counted as separate unique clicks, but if the same IP clicks on the same link,
     * it is not counted multiple times.
     *
     * @param  string|null  $startDate  Start date for filtering (optional).
     * @param  string|null  $endDate  End date for filtering (optional).
     * @return int The total count of unique clicks, or 0 if an error occurs.
     */
    public function getTotalUniqueClicks(?string $startDate = null, ?string $endDate = null): int
    {
        try {
            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $query = LinkHistory::distinct('ip_address', 'link_id');

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            return $query->count();
        } catch (Exception $e) {
            $this->logError('Error while retrieving unique click count', $e);

            return 0;
        }
    }

    /**
     * Get the average number of unique clicks per link.
     *
     * This method calculates the average number of unique clicks per link,
     * based on distinct combinations of IP address and link ID.
     * The calculation ensures that each IP address counts only once per link,
     * even if it clicks multiple times.
     *
     * @param  string|null  $startDate  Start date for filtering (optional).
     * @param  string|null  $endDate  End date for filtering (optional).
     * @return float The average number of unique clicks per link, or 0 if an error occurs.
     */
    public function getAvgClicksPerLink(): int
    {
        try {
            $totalUniqueClicks = LinkHistory::selectRaw('COUNT(DISTINCT ip_address) as unique_clicks')->first()->unique_clicks;
            $totalLinks = LinkHistory::distinct('link_id')->count();

            return $totalLinks > 0 ? round($totalUniqueClicks / $totalLinks, 0) : 0;
        } catch (Exception $e) {
            $this->logError('Error while calculating average clicks per link', $e);

            return 0;
        }
    }

    /**
     * Get the total number of links created in the last 7 days.
     *
     * @return int The total count of links created in the last 7 days, or 0 if an error occurs.
     */
    public function getTotalLinksByDate(string $startDate, string $endDate): int
    {
        try {

            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            return Link::whereBetween('created_at', [$startDate, $endDate])->count();
        } catch (Exception $e) {
            $this->logError('Error while retrieving total links by date', $e);

            return 0;
        }
    }

    /**
     * Get total clicks count within date range
     *
     * @param  string  $startDate  Start date (format: Y-m-d)
     * @param  string  $endDate  End date (format: Y-m-d)
     * @return int Returns:
     *             - Click count for date range
     *             - 0 if error occurs
     *
     * @throws \Exception Logs errors and returns 0 on failure
     */
    public function getTotalClicksByDate(string $startDate, string $endDate): int
    {
        try {
            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            return LinkHistory::whereBetween('created_at', [$startDate, $endDate])->count();
        } catch (Exception $e) {
            $this->logError('Error while retrieving total clicks by date', $e);

            return 0;
        }
    }

    /**
     * Get daily clicks breakdown within date range
     *
     * @param  string|null  $startDate  Start date (format: Y-m-d)
     * @param  string|null  $endDate  End date (format: Y-m-d)
     * @return array|null Returns:
     *                    - Array with date => clicks count pairs
     *                    - null if error occurs
     *
     * @throws \Exception Logs errors and returns null on failure
     */
    public function getDailyClicksByDate(?string $startDate, ?string $endDate): ?array
    {
        try {
            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $clicks = LinkHistory::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get();

            $clicksByDate = [];

            // Fill the array with clicks by day
            foreach ($clicks as $click) {
                $date = $click->date;
                $clicksByDate[$date] = $click->count;
            }

            // Fill empty dates with zero
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) { // while less or equal
                $dateStr = $currentDate->toDateString();
                if (! isset($clicksByDate[$dateStr])) {
                    $clicksByDate[$dateStr] = 0;
                }
                $currentDate->addDay();
            }

            ksort($clicksByDate);

            return $clicksByDate;
        } catch (Exception $e) {
            $this->logError('Error fetching daily clicks by link ID', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return null;
        }
    }

    /**
     * Get hourly clicks breakdown within date range
     *
     * @param  string|null  $startDate  Start date (format: Y-m-d)
     * @param  string|null  $endDate  End date (format: Y-m-d)
     * @return array|null Returns:
     *                    - Array with hour => clicks count pairs (0-23)
     *                    - null if error occurs
     *
     * @throws \Exception Logs errors and returns null on failure
     */
    public function getHourlyClicksByDate(?string $startDate, ?string $endDate): ?array
    {
        try {

            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $clicksByHour = array_fill(0, 24, 0);

            $clicks = LinkHistory::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->get();

            foreach ($clicks as $click) {
                $clicksByHour[(int) $click->hour] = $click->count;
            }

            return $clicksByHour;
        } catch (Exception $e) {
            $this->logError('Error fetching hourly clicks for link', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return null;
        }
    }

    /**
     * Get top countries by unique clicks within date range
     *
     * @param  string  $startDate  Start date (format: Y-m-d)
     * @param  string  $endDate  End date (format: Y-m-d)
     * @return iterable|null Returns:
     *                       - Collection of top 5 countries with click counts
     *                       - null if error occurs
     *
     * @throws \Exception Logs errors and returns null on failure
     */
    public function getTopCountriesByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            $limit = 5;

            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $query = DB::table('link_histories')
                ->select('country_name', DB::raw('COUNT(DISTINCT ip_address) as click_count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('country_name')
                ->orderByDesc('click_count')
                ->limit($limit)
                ->get();

            return $query->map(fn ($item) => [
                'country' => $item->country_name,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching top countries by unique clicks', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return null;
        }
    }

    /**
     * Get top browsers by unique clicks within date range
     *
     * @param  string  $startDate  Start date (format: Y-m-d)
     * @param  string  $endDate  End date (format: Y-m-d)
     * @return iterable|null Returns:
     *                       - Collection of top 5 browsers with click counts
     *                       - null if error occurs
     *
     * @throws \Exception Logs errors and returns null on failure
     */
    public function getTopBrowsersByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $query = DB::table('link_histories')
                ->select('browser', DB::raw('COUNT(DISTINCT ip_address) as click_count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('browser')
                ->orderByDesc('click_count')
                ->limit(5)
                ->get();

            return $query->map(fn ($item) => [
                'browser' => $item->browser,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching top browsers by unique clicks', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return null;
        }
    }

    /**
     * Get top operating systems by unique clicks within date range
     *
     * @param  string  $startDate  Start date (format: Y-m-d)
     * @param  string  $endDate  End date (format: Y-m-d)
     * @return iterable|null Returns:
     *                       - Collection of top 5 OS with click counts
     *                       - null if error occurs
     *
     * @throws \Exception Logs errors and returns null on failure
     */
    public function getTopOSByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            [$startDate, $endDate] = $this->parseAndValidateDateRange($startDate, $endDate);

            $query = DB::table('link_histories')
                ->select('os', DB::raw('COUNT(DISTINCT ip_address) as click_count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('os')
                ->orderByDesc('click_count')
                ->limit(5)
                ->get();

            return $query->map(fn ($item) => [
                'os' => $item->os,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching top operating systems by unique clicks', $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return null;
        }
    }
}
