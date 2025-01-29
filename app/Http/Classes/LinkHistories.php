<?php

namespace App\Http\Classes;

use App\Models\Link;
use Exception;
use App\Models\LinkHistory;
use Carbon\Carbon;
use InvalidArgumentException;
use App\Http\Traits\LogsErrors;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class LinkHistories extends Links
{
    use LogsErrors;
    /**
     * Get the total number of clicks (LinkHistory records) for links owned by a specific user.
     *
     * This method calculates the total count of LinkHistory entries associated with links
     * belonging to the specified user. If an error occurs, it returns `null`.
     *
     * @param int $user_id The ID of the user.
     * @return int|null The total number of clicks or null on failure.
     */
    public function getTotalClicksByUserId(int $user_id): ?int
    {
        try {
            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->count();
        } catch (Exception $e) {
            $this->logError("Invalid user ID provided", $e, ['user_id' => $user_id]);
            return null;
        }
    }



    /**
     * Get the total count of unique IP addresses for links owned by a specific user.
     *
     * This method calculates the number of distinct IP addresses in the LinkHistory
     * records associated with links belonging to the specified user. Returns `null` 
     * if an error occurs.
     *
     * @param int $user_id The ID of the user.
     * @return int|null The total count of unique IPs or null on failure.
     */
    public function getUniqueIpsByUserId(int $user_id): ?int
    {
        try {
            // Count the number of distinct IP addresses associated with the user's links
            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->distinct('ip_address')->count('ip_address');
        } catch (Exception $e) {
            $this->logError("Error fetching unique IPs", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Get the total clicks for links owned by a specific user on the current day.
     *
     * This method calculates the total number of LinkHistory records created today, 
     * associated with links owned by the specified user.
     *
     * @param int $user_id The ID of the user.
     * @return int|null The total number of clicks today or null on failure.
     */
    public function getTodayTotalClicksByUserId(int $user_id): ?int
    {
        try {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();

            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->whereBetween('created_at', [$startDate, $endDate])->count();
        } catch (Exception $e) {
            $this->logError("Error fetching today's total clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Get the top links by clicks for a specific user within the last 24 hours.
     *
     * This method retrieves the top 5 links with the most clicks from the LinkHistory 
     * records associated with links owned by the specified user. Returns `null` if an 
     * error occurs.
     *
     * @param int|null $user_id The ID of the user.
     * @return iterable|null The top 5 links with the most clicks or null on failure.
     */
    public function getTopLinksClicksByUserId(int $user_id): ?iterable
    {
        try {
            $limit = 5;

            $startDate = now()->subDay();
            $endDate = now();

            $topLinks = LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('link_id, COUNT(*) as click_count')
                ->groupBy('link_id')
                ->orderByDesc('click_count')
                ->take($limit)
                ->with('link')
                ->get()
                ->map(function ($item) {
                    return [
                        'url' => $item->link->destination ?? null,
                        'click_count' => $item->click_count,
                    ];
                });
            return $topLinks;
        } catch (Exception $e) {
            $this->logError("Error fetching top links by clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Get top countries by the number of unique clicks (distinct IPs) for a specific user.
     *
     * This method returns a list of countries with the count of unique clicks, 
     * ordered by the number of unique clicks in descending order.
     *
     * @param int $user_id The ID of the user for whom to fetch the data
     * @return \Illuminate\Support\Collection|null A collection of countries with their unique click counts, or null in case of error
     */
    public function getTopCountriesByUserId(int $user_id): ?iterable
    {
        try {
            $limit = 5;
            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
                ->selectRaw('country_name, COUNT(DISTINCT ip_address) as click_count')
                ->groupBy('country_name')
                ->orderByDesc('click_count')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'country' => $item->country_name,
                        'click_count' => $item->click_count,
                    ];
                });
        } catch (Exception $e) {
            $this->logError("Error fetching top countries by unique clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }



    /**
     * Get top browsers by the number of unique clicks (distinct IPs) for a specific user.
     *
     * This method returns a list of browsers with the count of unique clicks, 
     * ordered by the number of unique clicks in descending order.
     *
     * @param int $user_id The ID of the user for whom to fetch the data
     * @return \Illuminate\Support\Collection|null A collection of browsers with their unique click counts, or null in case of error
     */
    public function getTopBrowsersByUserId(int $user_id): ?iterable
    {
        try {
            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
                ->selectRaw('browser, COUNT(DISTINCT ip_address) as click_count')
                ->groupBy('browser')
                ->orderByDesc('click_count')
                ->get()
                ->map(function ($item) {
                    return [
                        'browser' => $item->browser,
                        'click_count' => $item->click_count,
                    ];
                });
        } catch (Exception $e) {
            $this->logError("Error fetching top browsers by unique clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Get top operating systems by the number of unique clicks (distinct IPs) for a specific user.
     *
     * This method returns a list of operating systems with the count of unique clicks, 
     * ordered by the number of unique clicks in descending order.
     *
     * @param int $user_id The ID of the user for whom to fetch the data
     * @return \Illuminate\Support\Collection|null A collection of operating systems with their unique click counts, or null in case of error
     */
    public function getTopOperatingSystemsByUserId(int $user_id): ?iterable
    {
        try {
            return LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
                ->selectRaw('os, COUNT(DISTINCT ip_address) as click_count')
                ->groupBy('os')
                ->orderByDesc('click_count')
                ->get()
                ->map(function ($item) {
                    return [
                        'os' => $item->os,
                        'click_count' => $item->click_count,
                    ];
                });
        } catch (Exception $e) {
            $this->logError("Error fetching top operating systems by unique clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }



    /**
     * Get the number of clicks per hour for a specific user on the current day, starting from 00:00.
     *
     * This method returns an array of 24 elements, each representing the number of clicks 
     * for the corresponding hour of the day.
     *
     * @param int $user_id The ID of the user for whom to fetch the hourly click data.
     * @return array|null An array of click counts per hour or null in case of an error.
     */
    public function getHourlyClicksByUserId(int $user_id): ?array
    {
        try {
            $clicksByHour = array_fill(0, 24, 0);
            $currentDate = now()->toDateString();

            $clicks = LinkHistory::whereHas('link', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
                ->whereDate('created_at', $currentDate)
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as click_count')
                ->groupBy('hour')
                ->get();

            foreach ($clicks as $click) {
                $clicksByHour[$click->hour] = $click->click_count;
            }
            return $clicksByHour;
        } catch (Exception $e) {
            $this->logError("Error fetching hourly clicks", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Get the number of clicks per hour for a specific link within a specified date range.
     *
     * This method returns an array of 24 elements (hours), each representing the number of clicks 
     * for the corresponding hour of the day, for the specified link within the given date range.
     *
     * @param int $link_id The ID of the link for which to fetch the hourly click data.
     * @param string|null $start_date The start date for the range, in Y-m-d format.
     * @param string|null $end_date The end date for the range, in Y-m-d format.
     * @return array|null An array of click counts per hour or null in case of error.
     */
    public function getHourlyClicksByLinkId(int $link_id, ?string $start_date, ?string $end_date): ?array
    {
        try {
            $clicksByHour = array_fill(0, 24, 0);

            // If dates are not specified, set the current day.
            $startDate = $start_date ? Carbon::parse($start_date)->startOfDay() : now()->startOfDay();
            $endDate = $end_date ? Carbon::parse($end_date)->endOfDay() : now()->endOfDay();

            $clicks = LinkHistory::whereHas('link', function ($query) use ($link_id) {
                $query->where('id', $link_id);
            })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->get();

            foreach ($clicks as $click) {
                $clicksByHour[(int) $click->hour] = $click->count;
            }

            return $clicksByHour;
        } catch (Exception $e) {
            $this->logError("Error fetching hourly clicks for link", $e, [
                'link_id' => $link_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);
            return null;
        }
    }


    /**
     * Get top metrics (e.g., countries, operating systems, browsers) for a specific link 
     * within a specified date range, grouped by the provided metric.
     *
     * This method returns a list of metrics (country, OS, or browser) with the count of unique clicks, 
     * ordered by the number of unique clicks in descending order.
     *
     * @param int $link_id The ID of the link for which to fetch the metrics.
     * @param string|null $start_date The start date for the range, in Y-m-d format.
     * @param string|null $end_date The end date for the range, in Y-m-d format.
     * @param string $groupBy The metric by which to group the results (e.g., 'country_name', 'os', 'browser').
     * @return \Illuminate\Support\Collection|null A collection of metrics with their unique click counts, or null in case of error.
     */
    public function getTopMetricsByLinkId(int $link_id, ?string $start_date, ?string $end_date, string $groupBy): ?iterable
    {
        try {
            $limit = 5;

            $startDate = $start_date ? Carbon::parse($start_date)->startOfDay() : now()->startOfDay();
            $endDate = $end_date ? Carbon::parse($end_date)->endOfDay() : now()->endOfDay();

            $metrics = LinkHistory::where('link_id', $link_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("$groupBy, COUNT(DISTINCT ip_address) as click_count")
                ->groupBy($groupBy)
                ->orderByDesc('click_count')
                ->limit($limit)
                ->get();

            return $metrics->map(function ($item) use ($groupBy) {
                return [
                    $groupBy => $item->$groupBy,
                    'click_count' => $item->click_count,
                ];
            });
        } catch (Exception $e) {
            $this->logError("Error fetching metrics by link ID", $e, [
                'link_id' => $link_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'groupBy' => $groupBy,
            ]);
            return null;
        }
    }


    /**
     * Get the number of clicks per day for a specific link within a given date range.
     * 
     * This method returns an associative array where the keys are dates and the values
     * are the total number of clicks for each corresponding day.
     *
     * @param int $link_id The ID of the link for which to fetch daily clicks.
     * @param string|null $start_date The start date for the range, in Y-m-d format.
     * @param string|null $end_date The end date for the range, in Y-m-d format.
     * @return array|null An associative array of daily click counts or null in case of error.
     */
    public function getDailyClicksByLinkId(int $link_id, ?string $start_date, ?string $end_date): ?array
    {
        try {
            $startDate = $start_date ? Carbon::parse($start_date)->startOfDay() : now()->startOfDay();
            $endDate = $end_date ? Carbon::parse($end_date)->endOfDay() : now()->endOfDay();

            $clicks = LinkHistory::where('link_id', $link_id)
                ->whereBetween('created_at', [$startDate, $endDate])
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
                'link_id' => $link_id,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
            return null;
        }
    }


    /**
     * Process the redirect by validating and tracking analytics data for a specific link.
     * 
     * This method checks if the provided data (host, path, user-agent, IP) matches an existing 
     * link in the database. It also tracks analytics data, such as the user's browser, platform, 
     * and country, before redirecting to the destination link.
     *
     * @param array $data An array of data containing the host, path, user-agent, and IP address.
     * @return array|null The destination URL in the form of an associative array with the key 'link', 
     *         or null if the link could not be found or analytics data is missing.
     */
    private function trackAnalytics(array $trackingData): array
    {
        try {
            $agent = new Agent();
            $ip = $trackingData['ip'];
            $user_agent = $trackingData['user-agent'];

            $agent->setUserAgent($user_agent);

            return [
                'country_name' => $this->getCountryName($ip),
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'device' => $agent->device(),
                'ip_address' => $ip,
                'user_agent' => $user_agent,
            ];
        } catch (Exception $e) {
            $this->logError('Error tracking analytics', $e, [
                'tracking_data' => $trackingData,
            ]);
            return [];
        }
    }


    /**
     * Process the redirect by validating and tracking analytics data for a specific link.
     * 
     * This method checks if the provided data (host, path, user-agent, IP) matches an existing 
     * link in the database. It also tracks analytics data, such as the user's browser, platform, 
     * and country, before redirecting to the destination link.
     *
     * @param array $data An array of data containing the host, path, user-agent, and IP address.
     * @return array|null The destination URL in the form of an associative array with the key 'link', 
     *         or null if the link could not be found or analytics data is missing.
     */
    public function processRedirect(array $data): ?array
    {
        try {
            $ip = $data['ip'];
            $domain = $data['host'];
            $short_name = $data['path'];
            $user_agent = $data['user-agent'];

            $analytics_data = $this->trackAnalytics($data);

            if (empty($analytics_data)) {
                return null;
            }
            $link = $this->getLinkByDomainAndShortName($domain, $short_name);

            if (!$link) {
                return null;
            }

            $this->createLinkHistory($link, $analytics_data, $ip, $user_agent);

            return ['link' => $link->destination];
        } catch (Exception $e) {
            $this->logError('Error during redirection process', $e, ['data' => $data]);
            return null;
        }
    }

    /**
     * Create a history record for the given link and analytics data.
     * 
     * This method stores a new history record for the provided link, capturing 
     * information such as the user's IP address, browser, OS, and country name.
     *
     * @param Link $link The link object to create the history for.
     * @param array $analytics_data An array containing analytics data like browser, platform, and country.
     * @param string $ip The IP address of the user.
     * @param string $user_agent The user agent string of the browser.
     * @return void
     */
    private function createLinkHistory(Link $link, array $analytics_data, string $ip, string $user_agent): void
    {
        $link->link_histories()->create([
            'country_name' => $analytics_data['country_name'],
            'ip_address' => $ip,
            'user_agent' => $user_agent,
            'browser' => $analytics_data['browser'],
            'os' => $analytics_data['platform'],
        ]);
    }


    private function getCountryName($ip)
    {
        $test_ip = "195.1.1.1";
        $response = Http::get("http://ipinfo.io/" . $test_ip . "/json");

        if ($response->successful()) {
            $data = $response->json();
            $countryName = $data['country'] ?? 'Unknown';
            return  $countryName;
        } else {
            $countryName = 'Other';
            return $countryName;
        }

        /*$countries = [
            'US' => 'United States',
            'UA' => 'Ukraine',
        ];*/
        //return $countries[$countryCode] ?? 'Unknown';
    }
}
