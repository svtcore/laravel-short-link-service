<?php

namespace App\Http\Controllers\Admin;

use App\Http\Contracts\Interfaces\AdminStatisticsServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dashboard\ShowRequest;
use App\Http\Traits\LogsErrors;
use App\Jobs\Admin\Dashboard\GetTopBrowsersJob;
use App\Jobs\Admin\Dashboard\GetTopCountriesJob;
use App\Jobs\Admin\Dashboard\GetTopPlatformsJob;
use App\Jobs\Admin\Dashboard\GetTotalClicksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalDaysActivityJob;
use App\Jobs\Admin\Dashboard\GetTotalLinksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalTimeActivityJob;
use App\Jobs\Admin\Dashboard\GetTotalUniqueClicksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalUsersByDateJob;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LogsErrors;

    /**
     * @var AdminStatisticsServiceInterface Statistics service instance
     */
    private $statsService = null;

    public function __construct(AdminStatisticsServiceInterface $statsService)
    {
        $this->middleware('role:admin');
        $this->statsService = $statsService;
        ini_set('max_execution_time', 1200);
    }

    /**
     * Display admin dashboard with summary statistics
     *
     * @return \Illuminate\View\View Returns dashboard view with statistics data including:
     *                               - Total links count
     *                               - Total clicks count
     *                               - Active links count
     *                               - Unique clicks count
     *                               - Total users count
     *                               - Average clicks per link
     *
     * @throws Exception If statistics data cannot be loaded
     */
    public function index(): View
    {
        try {
            return view('admin.dashboard')->with([
                'total_links' => $this->statsService->getTotalLinks(null),
                'total_clicks' => $this->statsService->getTotalClicks(),
                'total_active_links' => $this->statsService->getTotalLinks(true),
                'total_unique_clicks' => $this->statsService->getTotalUniqueClicks(null, null),
                'total_users' => $this->statsService->getTotalUsers(null, null),
                'total_avg_clicks' => $this->statsService->getAvgClicksPerLink(),
            ]);
        } catch (Exception $e) {
            $this->logError('Error while loading data on dashboard', $e);

            return view('admin.dashboard')->with([
                'error' => 'Could not load statistics. Please try again later.',
            ]);
        }
    }

    /**
     * Get filtered dashboard data for specified date range
     *
     * @param  ShowRequest  $request  Validated request containing optional date filters
     * @return \Illuminate\Http\JsonResponse Returns JSON with:
     *                                       - Links, clicks, users statistics by date
     *                                       - Daily and hourly activity charts data
     *                                       - Geographic, browser and platform distribution data
     *
     * @throws \Exception Logs errors and returns error response if data processing fails
     */
    public function show(ShowRequest $request): mixed
    {
        try {
            $validatedData = $request->validated();

            $startDate = Carbon::parse($validatedData['startDate'] ?? now()->subDay())->startOfDay();
            $endDate = Carbon::parse($validatedData['endDate'] ?? now())->endOfDay();

            $cacheJobs = [
                'total_links_by_date' => GetTotalLinksByDateJob::class,
                'total_clicks_by_date' => GetTotalClicksByDateJob::class,
                'total_unique_clicks_by_date' => GetTotalUniqueClicksByDateJob::class,
                'total_users_by_date' => GetTotalUsersByDateJob::class,
                'total_daily_clicks' => GetTotalDaysActivityJob::class,
                'total_time_clicks' => GetTotalTimeActivityJob::class,
                'chart_top_countries_by_date' => GetTopCountriesJob::class,
                'chart_top_browsers_by_date' => GetTopBrowsersJob::class,
                'chart_top_platforms_by_date' => GetTopPlatformsJob::class,
            ];

            $cacheData = [];
            foreach ($cacheJobs as $keySuffix => $jobClass) {
                $key = $keySuffix.'_'.$startDate->toDateString().'_'.$endDate->toDateString();
                $data = Cache::get($key);

                if (! $data) {
                    $jobClass::dispatchSync($startDate, $endDate, $this->statsService);
                    $data = Cache::get($key);
                }
                // rewrite new data cache
                $cacheData[$keySuffix] = $data;
            }

            if (in_array(null, $cacheData, true)) {
                return response()->json(['error' => 'Cache not found, please try again later']);
            }

            return response()->json([
                'total_links_by_date' => $cacheData['total_links_by_date'],
                'total_clicks_by_date' => $cacheData['total_clicks_by_date'],
                'total_unique_clicks_by_date' => $cacheData['total_unique_clicks_by_date'],
                'total_users_by_date' => $cacheData['total_users_by_date'],
                'chart_days_activity_data' => $cacheData['total_daily_clicks'],
                'chart_time_activity_data' => $cacheData['total_time_clicks'],
                'chart_geo_data' => $cacheData['chart_top_countries_by_date'],
                'chart_browser_data' => $cacheData['chart_top_browsers_by_date'],
                'chart_platform_data' => $cacheData['chart_top_platforms_by_date'],
            ]);
        } catch (Exception $e) {
            $this->logError('Error while updating data on dashboard', $e);

            return response()->json(['error' => 'An unexpected error occurred during data update']);
        }
    }
}
