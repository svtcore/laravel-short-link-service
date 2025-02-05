<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Classes\Links;
use App\Http\Classes\LinkHistories;
use App\Http\Classes\Users;
use App\Http\Requests\Admin\Links\DestroyRequest;
use App\Http\Requests\Admin\Links\ShowRequest;
use App\Http\Requests\Admin\Links\StoreRequest;
use App\Http\Requests\Admin\Links\UpdateRequest;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Http\Traits\LogsErrors;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller
{
    use LogsErrors;

    private $links_obj = null;
    private $links_hist_obj = null;
    private $users_obj = null;

    public function __construct(Links $links_obj, LinkHistories $links_hist_obj, Users $users_obj)
    {
        $this->middleware('role:admin');
        $this->links_obj = $links_obj;
        $this->links_hist_obj = $links_hist_obj;
        $this->users_obj = $users_obj;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.links.index')->with([
            'links' => $this->links_obj->getLinksList(),
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
    public function store(StoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user_id = Auth::id();

            if (!empty($validatedData['user_email'])) {
                $user = $this->users_obj->getUserByEmail($validatedData['user_email']);
                if (!$user) {
                    return redirect()->back()->withErrors(['error' => 'User not found with this email']);
                }
                $user_id = $user->id;
            }

            $url = $validatedData['url'] ?? null;
            $customName = $validatedData['custom_name'] ?? null;
            $ip = $request->ip();

            if (!$url) {
                return redirect()->back()->withErrors(['error' => 'Invalid URL']);
            }

            $result = $this->links_obj->generateShortName($url, $customName, $user_id, $ip);

            if (!is_null($result)) {
                return redirect()->back()->with('success', 'Link successfully shortened');
            }

            return redirect()->back()->withErrors(['error' => 'An error occurred while shortening the link']);
        } catch (Exception $e) {
            $this->logError('Error in store method', $e);

            return abort(500, 'An internal error occurred');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try {
            $data = [
                'id' => $id,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
            ];
            $validatedData = Validator::make($data, (new ShowRequest())->rules())->validate();

            $link_id = $validatedData['id'];
            $start_date = $validatedData['startDate'] ?? null;
            $end_date = $validatedData['endDate'] ?? null;

            $metricsData = $this->getLinkMetrics($link_id, $start_date, $end_date);

            return response()->json($metricsData);
        } catch (Exception $e) {
            $this->logError('Error fetching link statistics', $e, [
                'link_id' => $link_id,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);

            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    protected function getLinkMetrics(int $link_id, ?string $start_date, ?string $end_date): array
    {
        return [
            'link' => $this->links_hist_obj->getById($link_id),
            'total_clicks_by_date' => $this->links_hist_obj->getTotalClicksByLinkId($link_id, $start_date, $end_date),
            'total_unique_clicks_by_date' => $this->links_hist_obj->getUniqueIpsByLinkId($link_id, $start_date, $end_date),
            'active_days' => $this->links_hist_obj->getDailyClicksByLinkId($link_id, $start_date, $end_date),
            'active_hours' => $this->links_hist_obj->getHourlyClicksByLinkId($link_id, $start_date, $end_date),
            'top_countries' => $this->links_hist_obj->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'country_name'),
            'top_devices' => $this->links_hist_obj->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'os'),
            'top_browsers' => $this->links_hist_obj->getTopMetricsByLinkId($link_id, $start_date, $end_date, 'browser'),
        ];
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
    /**
     * Update an existing link.
     *
     * @param UpdateRequest $request The validated update request.
     * @param string $id The ID of the link to update.
     * @return RedirectResponse The response with success or error message.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = [
                'id' => $id,
                'url' => $request->editURL,
                'custom_name' => $request->editCustomName,
                'status' => $request->editStatus
            ];
            $validatedData = Validator::make($data, (new UpdateRequest())->rules())->validate();

            $result = $this->links_obj->updateLink(
                $validatedData['custom_name'],
                $validatedData['url'],
                $validatedData['status'],
                $id,
            );

            return $result
                ? redirect()->back()->with('success', 'Link successfully updated')
                : redirect()->back()->withErrors(['error' => 'An error occurred while updating the link']);
        } catch (Exception $e) {
            $this->logError('Error in update method', $e, ['id' => $id]);
            return abort(500, 'An internal error occurred');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = [
                'id' => $id,
            ];
            $validatedData = Validator::make($data, (new DestroyRequest())->rules())->validate();
            $result = $this->links_obj->destroyLink($validatedData['id']);
            if (!is_null($result)) {
                return redirect()->back()->with('success', 'Link and histories successfuly deleted');
            } else {
                return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the link']);
            }
        } catch (Exception $e) {
            $this->logError("Error deleting link", $e);
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the link']);
        }
    }
}
