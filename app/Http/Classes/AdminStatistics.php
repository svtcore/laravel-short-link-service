<?php

namespace App\Http\Classes;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use App\Models\Domain;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\LogsErrors;

class AdminStatistics
{
    use LogsErrors;

    /**
     * Get the total number of links created in the system.
     *
     * This method retrieves the total count of links stored in the database. 
     * It ensures that if an error occurs during the database query, the method 
     * gracefully handles it and returns 0.
     *
     * @return int The total count of links, or 0 if an error occurs.
     */
    public function getTotalLinks(bool $available = null): int
    {
        try {
            $query = Link::query();

            if (!is_null($available)) {
                $query->where('available', $available);
            }

            $count = $query->count();
            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving total links", $e);
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
            return ($activeLinksCount >= 0) ? $activeLinksCount : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving total active links", $e);
            return 0;
        }
    }

    public function getTotalUsers(?string $startDate = null, ?string $endDate = null): int
    {
        try {
            $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

            $query = User::query();
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            $count = $query->count();
            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving users count", $e);
            return 0;
        }
    }

    public function getTotalClicks(): int
    {
        try {
            $clicks = LinkHistory::count();
            return ($clicks >= 0) ? $clicks : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving click count", $e);
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
     * @param string|null $startDate Start date for filtering (optional).
     * @param string|null $endDate End date for filtering (optional).
     * @return int The total count of unique clicks, or 0 if an error occurs.
     */
    public function getTotalUniqueClicks(?string $startDate = null, ?string $endDate = null): int
    {
        try {
            $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

            $query = LinkHistory::distinct('ip_address', 'link_id');

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $clicks = $query->count();

            return ($clicks >= 0) ? $clicks : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving unique click count", $e);
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
     * @param string|null $startDate Start date for filtering (optional).
     * @param string|null $endDate End date for filtering (optional).
     * @return float The average number of unique clicks per link, or 0 if an error occurs.
     */
    public function getAvgClicksPerLink(string $startDate = null, string $endDate = null): float
    {
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $query = LinkHistory::selectRaw('link_id, COUNT(DISTINCT ip_address) as unique_clicks')
                ->groupBy('link_id');

            $uniqueClicksPerLink = $query->get();

            $totalUniqueClicks = $uniqueClicksPerLink->sum('unique_clicks');
            $totalLinks = $uniqueClicksPerLink->count();

            if ($totalLinks > 0) {
                return round($totalUniqueClicks / $totalLinks, 0);
            }

            return 0;
        } catch (Exception $e) {
            $this->logError("Error while calculating average clicks per link", $e);
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
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $count = Link::whereBetween('created_at', [$startDate, $endDate])->count();

            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving total links by date", $e);
            return 0;
        }
    }

    public function getTotalClicksByDate(string $startDate, string $endDate): int
    {
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $count = LinkHistory::whereBetween('created_at', [$startDate, $endDate])->count();

            return ($count >= 0) ? $count : 0;
        } catch (Exception $e) {
            $this->logError("Error while retrieving total clicks by date", $e);
            return 0;
        }
    }

    public function getDailyClicksByDate(?string $startDate, ?string $endDate): ?array
    {
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

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

            //Fill empty dates with zero
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) { // while less or equal
                $dateStr = $currentDate->toDateString();
                if (!isset($clicksByDate[$dateStr])) {
                    $clicksByDate[$dateStr] = 0;
                }
                $currentDate->addDay();
            }

            ksort($clicksByDate);

            return $clicksByDate;
        } catch (Exception $e) {
            $this->logError("Error fetching daily clicks by link ID", $e, [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return null;
        }
    }

    public function getHourlyClicksByDate(?string $startDate, ?string $endDate): ?array
    {
        try {

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

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
            $this->logError("Error fetching hourly clicks for link", $e, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            return null;
        }
    }

    public function getTopCountriesByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            $limit = 5;

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            //for faster loading instead of EQ
            $query = DB::table(DB::raw('(SELECT DISTINCT ip_address, country_name FROM link_histories WHERE created_at BETWEEN ? AND ?) as unique_ips'))
                ->selectRaw('country_name, COUNT(*) as click_count')
                ->groupBy('country_name')
                ->orderByDesc('click_count')
                ->limit($limit)
                ->setBindings([$startDate, $endDate])
                ->get();

            return $query->map(fn($item) => [
                'country' => $item->country_name,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching top countries by unique clicks", $e, ['start_date' => $startDate, 'end_date' => $endDate]);
            return null;
        }
    }


    public function getTopBrowsersByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $query = DB::table(DB::raw('(SELECT DISTINCT ip_address, browser FROM link_histories WHERE created_at BETWEEN ? AND ?) as unique_ips'))
                ->selectRaw('browser, COUNT(*) as click_count')
                ->groupBy('browser')
                ->orderByDesc('click_count')
                ->limit(5)
                ->setBindings([$startDate, $endDate])
                ->get();

            return $query->map(fn($item) => [
                'browser' => $item->browser,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching top browsers by unique clicks", $e, ['start_date' => $startDate, 'end_date' => $endDate]);
            return null;
        }
    }


    public function getTopOSByDate(string $startDate, string $endDate): ?iterable
    {
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $query = DB::table(DB::raw('(SELECT DISTINCT ip_address, os FROM link_histories WHERE created_at BETWEEN ? AND ?) as unique_ips'))
                ->selectRaw('os, COUNT(*) as click_count')
                ->groupBy('os')
                ->orderByDesc('click_count')
                ->limit(5)
                ->setBindings([$startDate, $endDate])
                ->get();

            return $query->map(fn($item) => [
                'os' => $item->os,
                'click_count' => $item->click_count,
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching top operating systems by unique clicks", $e, ['start_date' => $startDate, 'end_date' => $endDate]);
            return null;
        }
    }
}
