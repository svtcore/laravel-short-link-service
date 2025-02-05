<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Classes\AdminStatistics;
use App\Http\Requests\Admin\Dashboard\ShowRequest;
use Exception;
use App\Http\Traits\LogsErrors;
use App\Jobs\Admin\Dashboard\GetAggregatedStatisticsJob;
use App\Jobs\Admin\Dashboard\GetTopBrowsersJob;
use App\Jobs\Admin\Dashboard\GetTopCountriesJob;
use App\Jobs\Admin\Dashboard\GetTopPlatformsJob;
use Carbon\Carbon;
use App\Jobs\Admin\Dashboard\GetTotalLinksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalClicksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalDaysActivityJob;
use App\Jobs\Admin\Dashboard\GetTotalUniqueClicksByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalUsersByDateJob;
use App\Jobs\Admin\Dashboard\GetTotalTimeActivityJob;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use LogsErrors;

    private $stat_obj = null;

    public function __construct(AdminStatistics $stat_obj)
    {
        $this->middleware('role:admin');
        $this->stat_obj = $stat_obj;
        ini_set('max_execution_time', 1200);
    }


    public function index()
    {
        return view('admin.dashboard')->with([
            'total_links' => $this->stat_obj->getTotalLinks(null),
            'total_clicks' => $this->stat_obj->getTotalClicks(),
            'total_active_links' => $this->stat_obj->getTotalLinks(true),
            'total_unique_clicks' => $this->stat_obj->getTotalUniqueClicks(null, null),
            'total_users' => $this->stat_obj->getTotalUsers(null, null),
            'total_avg_clicks' => $this->stat_obj->getAvgClicksPerLink(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $startDate = $validatedData['startDate'] ?? now()->subDays(1);
            $endDate = $validatedData['endDate'] ?? now()->endOfDay();

            GetTotalLinksByDateJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTotalClicksByDateJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTotalUniqueClicksByDateJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTotalUsersByDateJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTotalDaysActivityJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTotalTimeActivityJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTopCountriesJob::dispatchSync($startDate, $endDate, $this->stat_obj);
            GetTopBrowsersJob::dispatchSync($startDate,$endDate, $this->stat_obj);
            GetTopPlatformsJob::dispatchSync($startDate, $endDate, $this->stat_obj);

            $totalLinksByDate = Cache::get('total_links_by_date');
            $totalClicksByDate = Cache::get('total_clicks_by_date');
            $totalUniqueClicksByDate = Cache::get('total_unique_clicks_by_date');
            $totalUsersByDate = Cache::get('total_users_by_date');
            $totalDaysActivity = Cache::get('total_daily_clicks');
            $totalTimeActivity = Cache::get('total_time_clicks');
            $topCountries = Cache::get('chart_top_countries_by_date');
            $topBrowsers = Cache::get('chart_top_browsers_by_date');
            $topPlarforms = Cache::get('chart_top_platforms_by_date');

            return response()->json([
                'total_links_by_date' => $totalLinksByDate,
                'total_clicks_by_date' => $totalClicksByDate,
                'total_unique_clicks_by_date' => $totalUniqueClicksByDate,
                'total_users_by_date' => $totalUsersByDate,
                'chart_days_activity_data' => $totalDaysActivity,
                'chart_time_activity_data' => $totalTimeActivity,
                'chart_geo_data' => $topCountries,
                'chart_browser_data' => $topBrowsers,
                'chart_platform_data' => $topPlarforms,
            ]);
        } catch (Exception $e) {
            $this->logError("Error while updating data on dashboard", $e);
            return response()->json(['error' => 'An unexpected erorr during updating data']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
