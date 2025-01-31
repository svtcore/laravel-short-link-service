<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Classes\AdminStatistics;
use App\Http\Requests\Admin\Dashboard\ShowRequest;
use Exception;
use App\Http\Traits\LogsErrors;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use LogsErrors;

    private $stat_obj = null;

    public function __construct(AdminStatistics $stat_obj)
    {
        $this->middleware('role:admin');
        $this->stat_obj = $stat_obj;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $startDate = now()->subDays(7);
            $endDate = now()->endOfDay();
            return view('admin.dashboard')->with([
                'total_links' => $this->stat_obj->getTotalLinks(null),
                'total_links_by_date' => $this->stat_obj->getTotalLinksByDate($startDate, $endDate),
                'total_clicks' => $this->stat_obj->getTotalClicks(),
                'total_clicks_by_date' => $this->stat_obj->getTotalClicksByDate($startDate, $endDate),
                'total_active_links' => $this->stat_obj->getTotalLinks(true),
                'total_unique_clicks' => $this->stat_obj->getTotalUniqueClicks(null,null),
                'total_unique_clicks_by_date' => $this->stat_obj->getTotalUniqueClicks($startDate, $endDate),
                'total_users' => $this->stat_obj->getTotalUsers(null, null),
                'total_users_by_date' => $this->stat_obj->getTotalUsers($startDate, $endDate),
                'total_avg_clicks' => $this->stat_obj->getAvgClicksPerLink(),
                'chart_days_activity_data' => $this->stat_obj->getDailyClicksByDate($startDate, $endDate),
                'chart_time_activity_data' => $this->stat_obj->getHourlyClicksByDate($startDate, $endDate),
                'chart_geo_data' => $this->stat_obj->getTopCountriesByDate($startDate, $endDate),
                'chart_browser_data' => $this->stat_obj->getTopBrowsersByDate($startDate, $endDate),
                'chart_platform_data' => $this->stat_obj->getTopOSByDate($startDate, $endDate),
            ]);
        } catch (Exception $e) {
            $this->logError("Error while showing dashboard", $e);
            return abort(500);
        }
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
        try{
            $validatedData = $request->validated();

            $startDate = $validatedData['startDate'];
            $endDate = $validatedData['endDate'];

            return response()->json([
                'total_links_by_date' => $this->stat_obj->getTotalLinksByDate($startDate, $endDate),
                'total_clicks_by_date' => $this->stat_obj->getTotalClicksByDate($startDate, $endDate),
                'total_unique_clicks_by_date' => $this->stat_obj->getTotalUniqueClicks($startDate, $endDate),
                'total_users_by_date' => $this->stat_obj->getTotalUsers($startDate, $endDate),
                'chart_days_activity_data' => $this->stat_obj->getDailyClicksByDate($startDate, $endDate),
                'chart_time_activity_data' => $this->stat_obj->getHourlyClicksByDate($startDate, $endDate),
                'chart_geo_data' => $this->stat_obj->getTopCountriesByDate($startDate, $endDate),
                'chart_browser_data' => $this->stat_obj->getTopBrowsersByDate($startDate, $endDate),
                'chart_platform_data' => $this->stat_obj->getTopOSByDate($startDate, $endDate),
            ]);
        }
        catch(Exception $e){
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
