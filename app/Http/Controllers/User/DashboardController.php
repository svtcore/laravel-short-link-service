<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\LogsErrors;

class DashboardController extends Controller
{

    use LogsErrors;

    /**
     * @var LinkServiceInterface $linkService Links service instance
     */
    protected $linkService = null;

    /**
     * @var LinkHistoryServiceInterface $linkHistoryService Link histories service instance
     */
    protected $linkHistoryService = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param LinkServiceInterface $links Links service instance
     * @param LinkHistoryServiceInterface $linkHistories Link histories service instance
     */
    public function __construct(LinkServiceInterface $links, LinkHistoryServiceInterface $linkHistories)
    {
        $this->middleware('role:user');
        $this->linkService = $links;
        $this->linkHistoryService = $linkHistories;
    }


    /**
     * Display user dashboard with statistics
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse Returns:
     * - Dashboard view with user statistics if successful
     * - Redirect to home page if error occurs
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function index()
    {
        try {
            $user_id = Auth::id();

            $userData = $this->fetchUserData($user_id);

            return $userData
                ? view('user.dashboard', $userData)
                : redirect()->route('home');

        } catch (Exception $e) {
            $this->logError("Dashboard Controller Error", $e, ['user_id' => Auth::id()]);
            return redirect()->route('home');
        }
    }


    /**
     * Fetch all user dashboard statistics
     *
     * @param int $user_id Authenticated user ID
     * @return array|null Returns array containing:
     * - username: User's name
     * - links_count: Total links created
     * - clicks_count: Total clicks across all links
     * - unique_clicks_count: Unique visitor clicks
     * - links_today_count: Today's click count
     * - top_links: Most visited links
     * - top_countries: Visitor countries
     * - top_browsers: Visitor browsers
     * - top_os: Visitor operating systems
     * - hours_activity: Hourly click activity
     * 
     * @throws \Exception Logs errors and returns null on failure
     */
    private function fetchUserData(int $user_id): ?array
    {
        try {
            $links_count = $this->linkService->getTotalUserLinks($user_id);
            $clicks_count = $this->linkHistoryService->getTotalClicksByUserId($user_id);
            $unique_clicks_count = $this->linkHistoryService->getUniqueIpsByUserId($user_id);
            $links_today_count = $this->linkHistoryService->getTodayTotalClicksByUserId($user_id);
            $top_links = $this->linkHistoryService->getTopLinksClicksByUserId($user_id);
            $top_countries = $this->linkHistoryService->getTopCountriesByUserId($user_id);
            $top_browsers = $this->linkHistoryService->getTopBrowsersByUserId($user_id);
            $top_os = $this->linkHistoryService->getTopOperatingSystemsByUserId($user_id);
            $hours_activity = $this->linkHistoryService->getHourlyClicksByUserId($user_id);

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
