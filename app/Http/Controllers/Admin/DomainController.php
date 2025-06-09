<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Contracts\Interfaces\DomainServiceInterface;
use App\Http\Requests\Admin\Domains\StoreRequest;
use App\Http\Traits\LogsErrors;
use App\Http\Requests\Admin\Domains\UpdateRequest;
use App\Http\Requests\Admin\Domains\DestroyRequest;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DomainController extends Controller
{
    use LogsErrors;

    /**
     * @var DomainServiceInterface $domainService Domain service instance
     */
    private $domainService = null;

    /**
     * Initialize controller with dependencies
     * 
     * @param DomainServiceInterface $domainService Domain service instance
     */
    public function __construct(DomainServiceInterface $domainService)
    {
        $this->middleware('role:admin');
        $this->domainService = $domainService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\View\View Returns domains index view with:
     * - domains: List of domains
     * 
     * @throws Exception Logs errors and returns error response if operation fails
     */
    public function index(): View
    {
        try {
            return view('admin.domains.index')->with([
                'domains' => $this->domainService->getDomainsList(null) ?? [],
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching domains list", $e);
            return view('admin.domains.index')->with([
                'domains' => [],
                'error' => 'An error occurred while loading domains. Please try again later.'
            ]);
        }
    }

    /**
     * Store a new domain
     * 
     * @param StoreRequest $request Validated request containing:
     * - domainName: The domain name to add
     * - domainStatus: Active/inactive status
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if domain was added
     * - Error message if domain exists or invalid
     * 
     * @throws \Exception Logs errors and returns error response if operation fails
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $result = $this->domainService->storeDomain($validatedData['domainName'], $validatedData['domainStatus']);

            if (is_null($result)) {
                return redirect()->back()->withErrors([
                    'domain' => 'An unexpected error occurred while storing the domain.',
                ]);
            }

            if (!$result) {
                return redirect()->back()->withErrors([
                    'domain' => 'Domain already exists or invalid format.',
                ]);
            }

            return redirect()->back()->with('success', 'Domain successfully added.');
        } catch (Exception $e) {
            $this->logError("Error storing domain", $e);
            return redirect()->back()->withErrors([
                'domain' => 'An internal server error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * Update an existing domain
     * 
     * @param Request $request Contains:
     * - domainName: New domain name
     * - domainStatus: New status
     * @param string $id Domain ID to update
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if domain was updated
     * - Error message if update failed
     * 
     * @throws \Exception Logs errors and returns error response if operation fails
     */
    public function update(UpdateRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            if (!$this->domainService->updateDomain($validatedData)) {
                return redirect()->back()->withErrors([
                    'domain' => 'Domain update failed.',
                ]);
            }

            return redirect()->back()->with('success', 'Domain successfully updated.');
        } catch (Exception $e) {
            $this->logError("Error updating domain", $e);
            return redirect()->back()->withErrors([
                'domain' => 'An internal error occurred. Please try again later.',
            ]);
        }
    }


    /**
     * Delete a domain and its related links
     * 
     * @param Request $request
     * @param string $id Domain ID to delete
     * 
     * @return \Illuminate\Http\RedirectResponse Redirects back with:
     * - Success message if domain was deleted
     * - Error message if deletion failed
     * 
     * @throws Exception Logs errors and returns error response if operation fails
     */
    public function destroy(DestroyRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $result = $this->domainService->destroyDomain($validatedData['id']);

            if (!$result) {
                return redirect()->back()->withErrors([
                    'domain' => 'An unexpected error occurred while deleting the domain.',
                ]);
            }

            return redirect()->back()->with('success', 'Domain and related links successfully deleted.');
        } catch (Exception $e) {
            $this->logError("Error deleting domain", $e);

            return redirect()->back()->withErrors([
                'domain' => 'An internal server error occurred. Please try again later.',
            ]);
        }
    }
}
