<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Search\LinkByIPRequest;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Domain;
use App\Models\User;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Contracts\Interfaces\DomainServiceInterface;
use App\Http\Requests\Admin\Search\CountRequest;
use App\Http\Requests\Admin\Search\DomainRequest;
use App\Http\Requests\Admin\Search\UserRequest;
use App\Http\Requests\Admin\Search\LinkRequest;
use App\Http\Requests\Admin\Search\LinkByDomainRequest;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Http\Traits\LogsErrors;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    use LogsErrors;
    /**
     * @var UserServiceInterface $userService Users service instance
     */
    private $userService = null;

    /**
     * @var LinkServiceInterface $linkService Links service instance
     */
    private $linkService = null;

    /**
     * @var DomainServiceInterface $domainService Domains service instance
     */
    private $domainService = null;

    /**
     * Initialize controller with service dependencies
     *
     * @param UserServiceInterface $userService Users service instance
     * @param LinkServiceInterface $linkService Links service instance
     * @param DomainServiceInterface $domainService Domains service instance
     */
    public function __construct(
        UserServiceInterface $userService,
        LinkServiceInterface $linkService,
        DomainServiceInterface $domainService
    ) {
        $this->middleware('role:admin');
        $this->userService = $userService;
        $this->linkService = $linkService;
        $this->domainService = $domainService;
    }

    /**
     * Get search counts across all categories
     * 
     * @param CountRequest $request Validated request containing search query
     * @return \Illuminate\Http\JsonResponse Returns JSON with:
     * - links: Count of matching links
     * - domains: Count of matching domains
     * - users: Count of matching users
     * 
     * @throws Exception Logs errors and returns error response
     */
    public function count(CountRequest $request): mixed
    {
        try {
            $validatedData = $request->validated();

            return response()->json([
                'links' => $this->linkService->searchLinks($validatedData['query'], true),
                'domains' => $this->domainService->searchDomains($validatedData['query'], true),
                'users' => $this->userService->searchUsers($validatedData['query'], true)
            ]);
        } catch (Exception $e) {
            $this->logError('Error while searching category', $e);
            return response()->json([
                'error' => 'Server Error',
            ], 500);
        }
    }

    /**
     * Search domains by query
     * 
     * @param DomainRequest $request Validated request containing search query
     * Returns domains search results view with:
     * - results: Matching domains
     * - query: Original search query
     * 
     * @throws Exception Logs errors and returns error response
     */
    public function domains(DomainRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $results = $this->domainService->searchDomains($validatedData['query'], false);
            return view('admin.search.results.domains')->with([
                'results' => $results ?? [],
                'query' => $validatedData['query']
            ]);

        } catch (Exception $e) {
            $this->logError('Error while searching domain', $e);
            return redirect()->back()->with([
                'error' => 'Server Error, try again',
            ]);
        }
    }

    /**
     * Search users by query
     * 
     * @param UserRequest $request Validated request containing search query
     * Returns users search results view with:
     * - results: Matching users
     * - query: Original search query
     * 
     * @throws \Exception Logs errors and returns error response
     */
    public function users(UserRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $results = $this->userService->searchUsers($validatedData['query'], false);

            return view('admin.search.results.users')->with([
                'results' => $results ?? [],
                'query' => $validatedData['query']
            ]);

        } catch (Exception $e) {
            $this->logError('Error while searching user', $e);
            return redirect()->back()->with([
                'error' => "Server Error, try again",
            ]);
        }
    }

    /**
     * Search links by query
     * 
     * @param LinkRequest $request Validated request containing search query
     * Returns links search results view with:
     * - results: Matching links
     * - query: Original search query
     * 
     * @throws \Exception Logs errors and returns error response
     */
    public function links(LinkRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $results = $this->linkService->searchLinks($validatedData['query'], false);

            return view('admin.search.results.links')->with([
                'results' => $results ?? [],
                'query' => $validatedData['query']
            ]);

        } catch (Exception $e) {
            $this->logError('Error while searching link', $e);
            return redirect()->back()->with([
                'error' => "Server Error, try again",
            ]);
        }
    }

    /**
     * Search links by domain ID
     * 
     * @param Request $request
     * @param string $id Domain ID to search links for
     * Returns links search results view with:
     * - results: Links belonging to domain
     * - query: Domain ID
     * 
     * @throws \Exception Logs errors and returns error response
     */
    public function linksByDomain(LinkByDomainRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $results = $this->linkService->searchByDomainId($validatedData['id']);
            return view('admin.search.results.links')->with([
                'results' => $results ?? [],
                'query' => $validatedData['id']
            ]);
        } catch (Exception $e) {
            $this->logError('Error while searching link by domain id', $e);
            return redirect()->back()->with([
                'error' => "Server Error, try again",
            ]);
        }
    }

    /**
     * Search links by user IP address
     * 
     * @param LinkByIPRequest $request Validated request containing IP address
     * Returns links search results view with:
     * - results: Links accessed by IP
     * - ip: Original IP address
     * 
     * @throws \Exception Logs errors and returns error response
     */
    public function linksByIP(LinkByIPRequest $request): RedirectResponse|View
    {
        try {
            $validatedData = $request->validated();

            $results = $this->linkService->searchByUserIP($validatedData['ip']);

            return view('admin.search.results.links')->with([
                'results' => $results ?? [],
                'ip' => $validatedData['ip']
            ]);
        } catch (Exception $e) {
            $this->logError('Error while searching links by user IP', $e);
            return redirect()->back()->with([
                'error' => "Server Error, try again",
            ]);
        }
    }
}
