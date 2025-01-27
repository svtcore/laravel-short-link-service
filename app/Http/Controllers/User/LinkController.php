<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Classes\LinkHistories;
use Illuminate\Http\Request;
use App\Http\Classes\Links;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\LogsErrors;
use App\Http\Requests\User\Links\StoreRequest;
use App\Http\Requests\User\Links\ShowRequest;
use App\Http\Requests\User\Links\EditRequest;
use App\Http\Requests\User\Links\UpdateRequest;

class LinkController extends Controller
{
    use LogsErrors;

    private $links_obj = null;
    private $links_obj_hist = null;

    public function __construct(Links $links_obj, LinkHistories $links_obj_hist)
    {
        $this->middleware('role:user')->except(['store']);
        $this->links_obj = $links_obj;
        $this->links_obj_hist = $links_obj_hist;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $links = $this->links_obj->getUserLinksData(Auth::user()->id) ?? [];

            return view('user.links')->with(['links' => $links]);
        } catch (Exception $e) {
            $this->logError("Error fetching user links", $e, ['user_id' => Auth::id()]);
            return redirect()->route('user.links.index')->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Store a newly created link in the database.
     *
     * This method accepts a URL and an optional custom name for the short link.
     * It validates the input, generates the short link, and saves it to the database.
     * The link is associated with the user if the user is authenticated, otherwise it is not.
     *
     * @param \App\Http\Requests\User\StoreRequest $request The request containing the URL and custom name.
     * @return \Illuminate\Http\JsonResponse The response containing the status of the operation.
     */
    public function store(StoreRequest $request)
    {
        try {
            $url = $request->validated()['url'];
            $custom_name = $request->validated()['custom_name'] ?? null;

            // Check if the user is authenticated. If so, associate the link with the user, otherwise set user_id to null.
            $user_id = Auth::check() ? Auth::id() : null;

            return $this->links_obj->generateShortName($url, $custom_name, $user_id);
        } catch (Exception $e) {
            $this->logError("Error while creating short link", $e, ['url' => $url, 'user_id' => $user_id ?? 'guest']);
            return response()->json(['error' => 'An error occurred while creating the link.'], 500);
        }
    }




    /**
     * Display the specified resource.
     *
     * This method retrieves and returns various statistics related to a specific link.
     * It ensures that the requested link belongs to the authenticated user, and it applies
     * the appropriate filters if provided. If an error occurs, a JSON response with an error 
     * message will be returned.
     *
     * @param ShowRequest $request The incoming request instance.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the requested data or an error message.
     */
    public function show(ShowRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $link_id = $validatedData['id'];
            $user_id = Auth::id();
            $start_date = $validatedData['startDate'] ?? null;
            $end_date = $validatedData['endDate'] ?? null;

            if (!$this->links_obj->isOwnUser($link_id, $user_id)) {
                return response()->json(['error' => 'Access denied.'], 403);
            }

            $metricsData = $this->getLinkMetrics($link_id, $start_date, $end_date);

            return response()->json($metricsData);
        } catch (Exception $e) {
            $this->logError('Error fetching link statistics', $e, [
                'link_id' => $link_id,
                'user_id' => $user_id,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);

            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    /**
     * Helper method to retrieve link metrics.
     *
     * @param int $link_id The ID of the link.
     * @param string|null $start_date The start date filter.
     * @param string|null $end_date The end date filter.
     * @return array The metrics data for the link.
     */
    protected function getLinkMetrics(int $link_id, ?string $start_date, ?string $end_date): array
    {
        return [
            'active_days' => $this->links_obj_hist->getDailyClicksByLinkId($link_id, $start_date, $end_date),
            'active_hours' => $this->links_obj_hist->getHourlyClicksByLinkId($link_id, $start_date, $end_date),
            'top_countries' => $this->links_obj_hist->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'country_name'),
            'top_devices' => $this->links_obj_hist->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'os'),
            'top_browsers' => $this->links_obj_hist->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'browser'),
        ];
    }


    /**
     * Show the form for editing the specified resource.
     *
     * This method retrieves the link data by its ID, ensuring the link exists and
     * belongs to the authenticated user. The request is first validated to ensure 
     * the ID is valid and exists in the database.
     *
     * @param \App\Http\Requests\User\Links\EditRequest $request The incoming request instance, containing the validated parameters.
     * @param string $id The ID of the link to be edited.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the link data or an error message.
     */
    public function edit(EditRequest $request, string $id)
    {
        try {
            //validate id through EditRequest->withValidator
            // Check if the authenticated user owns the link
            if (!$this->links_obj->isOwnUser($id, Auth::id())) {
                return response()->json(['error' => 'Access denied.'], 403);
            }

            $link = $this->links_obj->getById($id);

            return response()->json([
                'link_data' => $link,
            ]);
        } catch (Exception $e) {
            $this->logError('Error fetching link for editing', $e, ['link_id' => $id]);
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * Updates the provided link resource if it belongs to the authenticated user.
     *
     * @param \App\Http\Requests\UpdateLinkRequest $request The validated request instance.
     * @param string $id The ID of the link to update.
     * @return \Illuminate\Http\JsonResponse The JSON response with the operation status or error message.
     */
    public function update(UpdateRequest $request, string $id)
    {
        try {
            if (!$this->links_obj->isOwnUser($id, Auth::id())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden',
                ], 403);
            }

            $result = $this->links_obj->update(
                $request->validated('custom_name'),
                $request->validated('destination'),
                $request->validated('access'),
                $id
            );

            if ($result) {
                session()->flash('success', 'The link has been updated successfully.');
            } else {
                session()->flash('error', 'Failed to update the link.');
            }

            return response()->json([
                'status' => $result,
                'message' => $result ? 'Link updated successfully.' : 'Failed to update link.',
            ], $result ? 200 : 500);
        } catch (\Exception $e) {
            $this->logError('Error updating link', $e, ['link_id' => $id, 'user_id' => Auth::id()]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred while updating the link.',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * Deletes the link if it belongs to the authenticated user.
     *
     * @param string $id The ID of the link to delete.
     * @return \Illuminate\Http\JsonResponse The response indicating the result of the operation.
     */
    public function destroy(string $id)
    {
        try {

            if (!$this->links_obj->isOwnUser($id, Auth::id())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access forbiden',
                ], 403);
            }

            $result = $this->links_obj->destroy($id);

            if ($result) {
                return redirect()->route('user.links.index')->with('success', 'Link successfully deleted.');
            } else {
                return redirect()->route('user.links.index')->withErrors(['error' => 'Oops! Something went wrong and we couldn\'t delete the link.']);
            }
        } catch (\Exception $e) {
            $this->logError('Error deleting link', $e, ['link_id' => $id, 'user_id' => Auth::id()]);
            return redirect()->route('user.links.index')->withErrors(['error' => 'An unexpected error occurred while deleting the link.']);
        }
    }
}
