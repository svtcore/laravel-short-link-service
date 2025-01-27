<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Classes\LinkHistories;
use App\Http\Classes\Links;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\LogsErrors;

class DashboardController extends Controller
{

    use LogsErrors;

    protected $links_obj = null;
    protected $links_hist_obj = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Links $links, LinkHistories $linkHistories)
    {
        $this->middleware('role:user');
        $this->links_obj = $links;
        $this->links_hist_obj = $linkHistories;
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            $user_id = Auth::id();

            $userData = $this->fetchUserData($user_id);

            if ($userData) {
                return view('user.dashboard')->with($userData);
            }
            return abort(500);
        } catch (Exception $e) {
            $this->logError("Dashboard Controller Error", $e, ['user_id' => Auth::id()]);
            return abort(500);
        }
    }


    /**
     * Fetch user-related data in one go.
     * This method consolidates all user data retrieval into a single query.
     *
     * @param int $user_id
     * @return array|null
     */
    private function fetchUserData(int $user_id): ?array
    {
        try {
            $links_count = $this->links_obj->getTotalUserLinks($user_id);
            $clicks_count = $this->links_hist_obj->getTotalClicksByUserId($user_id);
            $unique_clicks_count = $this->links_hist_obj->getUniqueIpsByUserId($user_id);
            $links_today_count = $this->links_hist_obj->getTodayTotalClicksByUserId($user_id);
            $top_links = $this->links_hist_obj->getTopLinksClicksByUserId($user_id);
            $top_countries = $this->links_hist_obj->getTopCountriesByUserId($user_id);
            $top_browsers = $this->links_hist_obj->getTopBrowsersByUserId($user_id);
            $top_os = $this->links_hist_obj->getTopOperatingSystemsByUserId($user_id);
            $hours_activity = $this->links_hist_obj->getHourlyClicksByUserId($user_id);

            return [
                'username' => Auth::user()->name ?? 'user',
                'links_count' => $links_count ?? 0,
                'clicks_count' => $clicks_count ?? 0,
                'unique_clicks_count' => $unique_clicks_count ?? 0,
                'links_today_count' => $links_today_count ?? 0,
                'top_links' => $top_links ?? [],
                'top_countries' => $top_countries ?? [],
                'top_browsers' => $top_browsers ?? [],
                'top_os' => $top_os ?? [],
                'hours_activity' => $hours_activity ?? [],
            ];
        } catch (Exception $e) {
            $this->logError("Error fetching user data", $e, ['user_id' => Auth::id()]);
            return null;
        }
    }
}
