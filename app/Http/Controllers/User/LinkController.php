<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use Illuminate\Http\Request;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\LogsErrors;
use App\Http\Requests\User\Links\StoreRequest;
use App\Http\Requests\User\Links\ShowRequest;
use App\Http\Requests\User\Links\EditRequest;
use App\Http\Requests\User\Links\RedirectRequest;
use App\Http\Requests\User\Links\UpdateRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\User\Links\DeleteRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * Initialize controller with service dependencies
     *
     * @param LinkServiceInterface $linkService Links service instance
     * @param LinkHistoryServiceInterface $linkHistoryService Link histories service instance
     */
    public function __construct(LinkServiceInterface $linkService, LinkHistoryServiceInterface $linkHistoryService)
    {
        $this->middleware('role:user')->except(['store', 'redirect']);
        $this->linkService = $linkService;
        $this->linkHistoryService = $linkHistoryService;
    }


    /**
     * Display list of user's links with statistics
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse Returns:
     * - View with links data if successful
     * - Redirect back with error message on failure
     *
     * @throws \Exception Logs errors and returns error response
     */
    public function index()
    {
        try {
            $links = $this->linkService->getUserLinksData(Auth::id()) ?? [];
            return view('user.links')->with([
                'links' => $links,
            ]);

        } catch (Exception $e) {
            $this->logError("Error fetching user links", $e, ['user_id' => Auth::id()]);
            return back()->with('error', 'Failed to load links. Please try again.');
        }
    }

    /**
     * Create new short link
     *
     * @param StoreRequest $request Contains validated:
     * - url: Original URL to shorten
     * - custom_name: Optional custom short name
     * - from_modal: Flag if request came from modal
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Returns:
     * - JSON with short link data if successful
     * - Redirect to links index with success message if from modal
     * - JSON error response on failure
     *
     * @throws \Exception Logs errors and returns error response
     */
    public function store(StoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $url = $validatedData['url'];
            $custom_name = $validatedData['custom_name'] ?? null;
            $from_modal = $validatedData['from_modal'] ?? false;
            $ip = $request->ip();

            // Check if the user is authenticated. If so, associate the link with the user, otherwise set user_id to null.
            $user_id = Auth::check() ? Auth::id() : null;

            $destinationUrl = $this->linkService->generateShortName($url, $custom_name, $user_id, $ip) ?? null;
            if (empty($destinationUrl['short_name'])) {
                throw new Exception("Short name generation failed");
            }

            if ($from_modal) {
                return redirect()->route('user.links.index')->with('success', 'Link successfully shortened');
            }
            return response()->json($destinationUrl);
        } catch (Exception $e) {
            $this->logError("Error while creating short link", $e, ['url' => $url, 'user_id' => $user_id ?? 'guest']);
            return response()->json(['error' => 'An error occurred while creating the link.'], 500);
        }
    }

    /**
     * Show link statistics
     *
     * @param ShowRequest $request Contains validated:
     * - id: Link ID
     * - startDate: Optional start date filter
     * - endDate: Optional end date filter
     *
     * @return \Illuminate\Http\JsonResponse Returns:
     * - JSON with link metrics data if successful
     * - JSON error response if invalid date range or access denied
     *
     * @throws \Exception Logs errors and returns error response
     */
    public function show(ShowRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $link_id = $validatedData['id'];
            $user_id = Auth::id();
            $start_date = $validatedData['startDate'] ?? null;
            $end_date = $validatedData['endDate'] ?? null;

            if ($start_date && $end_date && $start_date > $end_date) {
                return response()->json(['error' => 'Invalid date range.'], 400);
            }

            if (!$this->linkService->isOwnUser($link_id, $user_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden',
                ], 403);
            }

            $metricsData = $this->getLinkMetrics($link_id, $start_date, $end_date);
            return response()->json($metricsData);

        } catch (Exception $e) {
            $this->logError('Error fetching link statistics', $e);
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    /**
     * Fetch link metrics data
     *
     * @param int $link_id Link ID to get metrics for
     * @param string|null $start_date Optional start date filter
     * @param string|null $end_date Optional end date filter
     * @return array Returns metrics including:
     * - active_days: Daily click counts
     * - active_hours: Hourly click counts
     * - top_countries: Visitor countries
     * - top_devices: Visitor devices
     * - top_browsers: Visitor browsers
     *
     * @throws \Exception Logs errors and returns empty array on failure
     */
    protected function getLinkMetrics(int $link_id, ?string $start_date, ?string $end_date): array
    {
        try {
            return [
                'active_days' => $this->linkHistoryService->getDailyClicksByLinkId($link_id, $start_date, $end_date),
                'active_hours' => $this->linkHistoryService->getHourlyClicksByLinkId($link_id, $start_date, $end_date),
                'top_countries' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'country_name'),
                'top_devices' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'os'),
                'top_browsers' => $this->linkHistoryService->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'browser'),
            ];
        } catch (Exception $e) {
            $this->logError('Error fetching link metrics', $e, ['link_id' => $link_id]);
            return [];
        }
    }

    /**
     * Get link data for editing
     *
     * @param string $id Link ID to edit
     * @return \Illuminate\Http\JsonResponse Returns:
     * - JSON with link data if successful
     * - JSON error response if validation fails or access denied
     *
     * @throws \Exception Logs errors and returns error response
     */
    public function edit(string $id)
    {
        try {
            $data = [
                'id' => $id,
            ];

            $validator = Validator::make($data, (new EditRequest())->rules());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error1',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validatedData = $validator->validated();

            // Check if the authenticated user owns the link
            if (!$this->linkService->isOwnUser($validatedData['id'], Auth::id())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden.',
                ], 403);
            }

            $link = $this->linkService->getById($validatedData['id']);

            return response()->json([
                'link_data' => $link,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching link for editing', $e, ['link_id' => $id]);

            return response()->json([
                'error' => 'An error occurred while processing your request.',
            ], 500);
        }
    }


    /**
     * Update link data
     *
     * @param Request $request Contains:
     * - custom_name: New custom short name
     * - destination: New destination URL
     * - access: New access level
     * @param string $id Link ID to update
     * @return \Illuminate\Http\JsonResponse Returns:
     * - JSON with update status if successful
     * - JSON error response if validation fails or access denied
     *
     * @throws \Exception Logs errors and returns error response
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = [
                'id' => $id,
                'custom_name' => $request->input('custom_name'),
                'destination' => $request->input('destination'),
                'access' => $request->input('access'),
            ];

            $validator = Validator::make($data, (new UpdateRequest())->rules());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            // Check if the authenticated user owns the link
            if (!$this->linkService->isOwnUser($validatedData['id'], Auth::id())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden.',
                ], 403);
            }

            $result = $this->linkService->updateLink(
                $validatedData['custom_name'],
                $validatedData['destination'],
                $validatedData['access'],
                $validatedData['id']
            );

            return response()->json([
                'status' => $result,
                'message' => $result ? 'Link updated successfully.' : 'Failed to update link.',
            ], $result ? 200 : 500);

        } catch (Exception $e) {
            $this->logError('Error updating link', $e, ['link_id' => $id, 'user_id' => Auth::id()]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred while updating the link.',
            ], 500);
        }
    }

    /**
     * Delete link
     *
     * @param string $id Link ID to delete
     * @return \Illuminate\Http\RedirectResponse Returns:
     * - Redirect to links index with success message if successful
     * - Redirect with error message if fails
     *
     * @throws \Exception Logs errors and redirects on failure
     */
    public function destroy(string $id)
    {
        try {

            $data = [
                'id' => $id,
            ];

            $validator = Validator::make($data, (new DeleteRequest())->rules());

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            // Check if the authenticated user owns the link
            if (!$this->linkService->isOwnUser($validatedData['id'], Auth::id())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden.',
                ], 403);
            }

            $deleted = $this->linkService->destroyLink($id);

            if (!$deleted) {
                throw new Exception('Failed to delete link');
            }

            return redirect()->route('user.links.index')
                ->with('success', 'Link successfully deleted');

        } catch (Exception $e) {
            $this->logError('Error deleting link', $e, ['link_id' => $id, 'user_id' => Auth::id()]);
            return redirect()->route('user.links.index')->withErrors(['error' => 'An unexpected error occurred while deleting the link.']);
        }
    }

    /**
     * Handle link redirection
     *
     * @param RedirectRequest $request Contains:
     * - host: Request host
     * - path: Short link path
     * - user_agent: Visitor's user agent
     * - ip: Visitor's IP address
     * @return \Illuminate\Http\RedirectResponse Returns:
     * - Redirect to destination URL if found
     * - 404 if link not found
     * - 500 on internal error
     *
     * @throws \Exception Logs errors and returns appropriate HTTP status
     */
    public function redirect(RedirectRequest $request): mixed
    {
        try {
            $validated = $request->validated();

            $result = $this->linkHistoryService->processRedirect($validated);

            if (empty($result['link'])) {
                throw new Exception('Destination URL not found');
            }

            return redirect()->away($result['link']);

        } catch (NotFoundHttpException $e) {
            abort(404, 'Link not found or expired');
        } catch (Exception $e) {
            $this->logError('Redirect failed', $e, [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            abort(500, 'Internal redirect error');
        }
    }
}
