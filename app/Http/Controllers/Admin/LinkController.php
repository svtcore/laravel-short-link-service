<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Requests\Admin\Links\DestroyRequest;
use App\Http\Requests\Admin\Links\ShowRequest;
use App\Http\Requests\Admin\Links\StoreRequest;
use App\Http\Requests\Admin\Links\UpdateRequest;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Http\Traits\LogsErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class LinkController extends Controller
{
    use LogsErrors;

    /**
     * @var LinkServiceInterface $linkService Links service instance
     */
    private $linkService = null;

    /**
     * @var LinkHistoryServiceInterface $linkHistoryService Link histories service instance
     */
    private $linkHistoryService = null;

    /**
     * @var UserServiceInterface $userService Users service instance
     */
    private $userService = null;

    /**
     * Initialize controller with dependencies
     * 
     * @param LinkServiceInterface $linkService Links service instance
     * @param LinkHistoryServiceInterface $linkHistoryService Link histories service instance
     * @param UserServiceInterface $userService Users service instance
     */
    public function __construct(LinkServiceInterface $linkService, LinkHistoryServiceInterface $linkHistoryService, UserServiceInterface $userService)
    {
        $this->middleware('role:admin');
        $this->linkService = $linkService;
        $this->linkHistoryService = $linkHistoryService;
        $this->userService = $userService;
    }

    /**
     * Display list of all links with statistics
     *
     * @return \Illuminate\View\View Returns:
     * - View with links data if successful
     * - View with error message if fails
     *
     * @throws \Exception Logs errors and returns error view
     */
    public function index(): View
    {
        try {
            return view('admin.links.index')->with([
                'links' => $this->linkService->getLinksList() ?? [],
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching links list', $e);
            return view('admin.links.index')->with([
                'links' => [],
                'error' => 'Failed to load links. Please try again later.'
            ]);
        }
    }


    /**
     * Create a new shortened link
     * 
     * @param StoreRequest $request Validated request containing:
     * - url: Original URL to shorten
     * - custom_name: Optional custom short name
     * - user_email: Optional user email to associate with link
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if link was created
     * - Error message if creation failed
     * 
     * @throws Exception Logs errors and returns error response if operation fails
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $userId = $this->resolveUserId($validated['user_email'] ?? null);

            $result = $this->linkService->generateShortName(
                $validated['url'],
                $validated['custom_name'] ?? null,
                $userId,
                $request->ip()
            );

            if ($result === null) {
                return redirect()->back()->withErrors(['error' => 'An error occurred while shortening the link']);
            }

            return redirect()->back()->with('success', 'Link successfully shortened');
        } catch (Exception $e) {
            $this->logError('Error in store method', $e);
            return redirect()->back()->withErrors(['error' => 'An error occurred while shortening the link']);
        }
    }
    private function resolveUserId(?string $email): int
    {
        if (!$email) {
            return Auth::id();
        }

        $user = $this->userService->getUserByEmail($email);
        if (!$user) {
            throw new Exception('User not found with this email');
        }

        return $user->id;
    }



    /**
     * Get link statistics and metrics
     * 
     * @param Request $request May contain:
     * - startDate: Optional start date filter
     * - endDate: Optional end date filter
     * @param string $id Link ID to get stats for
     * 
     * @return \Illuminate\Http\JsonResponse Returns JSON with:
     * - Link details
     * - Click statistics
     * - Geographic and device metrics
     * 
     * @throws \Exception Logs errors and returns error response if operation fails
     */
    public function show(ShowRequest $request): JsonResponse
    {
        $link_id = null;
        $start_date = null;
        $end_date = null;

        try {
            $validatedData = $request->validated();

            $link_id = (int) $validatedData['id'];
            $start_date = $validatedData['startDate'] ?? null;
            $end_date = $validatedData['endDate'] ?? null;

            $link = $this->linkHistoryService->getById($link_id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }

            $metricsData = $this->getLinkMetrics($link_id, $start_date, $end_date);

            return response()->json($metricsData);
        } catch (Exception $e) {
            $this->logError('Error fetching link statistics', $e, [
                'link_id' => $link_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    protected function getLinkMetrics(int $link_id, ?string $start_date, ?string $end_date): array
    {
        $link = $this->linkHistoryService->getById($link_id);
        if (!$link) {
            throw new Exception("Link with id {$link_id} not found");
        }

        return [
            'link' => $link,
            'total_clicks_by_date' => $this->linkHistoryService->getTotalClicksByLinkId($link_id, $start_date, $end_date),
            'total_unique_clicks_by_date' => $this->linkHistoryService->getUniqueIpsByLinkId($link_id, $start_date, $end_date),
            'active_days' => $this->linkHistoryService->getDailyClicksByLinkId($link_id, $start_date, $end_date),
            'active_hours' => $this->linkHistoryService->getHourlyClicksByLinkId($link_id, $start_date, $end_date),
            'top_countries' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'country_name'),
            'top_devices' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'os'),
            'top_browsers' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'browser'),
        ];
    }



    /**
     * Update an existing link
     * 
     * @param Request $request Contains:
     * - editURL: New destination URL
     * - editCustomName: New custom short name
     * - editStatus: New status
     * @param string $id Link ID to update
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if link was updated
     * - Error message if update failed
     * 
     * @throws \Exception Logs errors and returns error response if operation fails
     */
    public function update(UpdateRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->linkService->updateLink(
                $validatedData['custom_name'],
                $validatedData['url'],
                $validatedData['status'],
                $validatedData['id']
            );

            if ($result) {
                return redirect()->back()->with('success', 'Link successfully updated');
            }

            return redirect()->back()->withErrors([
                'update_error' => 'An error occurred while updating the link',
            ]);
        } catch (Exception $e) {
            $this->logError('Error in update method', $e);
            return redirect()->back()->withErrors([
                'update_error' => 'An internal server error occurred. Please try again later.',
            ]);
        }
    }



    /**
     * Delete a link and its click history
     * 
     * @param string $id Link ID to delete
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if link was deleted
     * - Error message if deletion failed
     * 
     * @throws \Exception Logs errors and returns error response if operation fails
     */
    public function destroy(DestroyRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->linkService->destroyLink($validatedData['id']);

            if ($result === true) {
                return redirect()->back()->with('success', 'Link and histories successfully deleted');
            }

            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the link']);
        } catch (Exception $e) {
            $this->logError('Error deleting link', $e);
            return redirect()->back()->withErrors(['error' => 'An internal server error occurred. Please try again later.']);
        }
    }
}
