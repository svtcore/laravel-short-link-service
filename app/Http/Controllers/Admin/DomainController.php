<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Classes\Domains;
use App\Http\Requests\Admin\Domains\StoreRequest;
use App\Http\Traits\LogsErrors;
use App\Http\Requests\Admin\Domains\UpdateRequest;
use App\Http\Requests\Admin\Domains\DestroyRequest;
use Exception;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    use LogsErrors;

    private $domain_obj = null;

    public function __construct(Domains $domain_obj)
    {
        $this->middleware('role:admin');
        $this->domain_obj = $domain_obj;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.domains.index')->with([
            'domains' => $this->domain_obj->getDomainsList(null),
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
     * Store a newly created domain in storage.
     */
    public function store(StoreRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $result = $this->domain_obj->storeDomain($validatedData['domainName'], $validatedData['domainStatus']);

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        try {
            $data = [
                'id' => $id,
                'domainName' => $request->domainName,
                'domainStatus' => $request->domainStatus,
            ];
            $validatedData = Validator::make($data, (new UpdateRequest())->rules())->validate();

            $result = $this->domain_obj->updateDomain($validatedData);

            if (is_null($result)) {
                return redirect()->back()->withErrors([
                    'domain' => 'An unexpected error occurred while updating the domain.',
                ]);
            }

            return redirect()->back()->with('success', 'Domain successfully updated.');
        } catch (Exception $e) {
            $this->logError("Error updating domain", $e);
            return redirect()->back()->withErrors([
                'domain' => 'An internal server error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try{
            $data = [
                'id' => $id,
            ];
            $validatedData = Validator::make($data, (new DestroyRequest())->rules())->validate();
            $result = $this->domain_obj->destroyDomain($validatedData['id']);
            if (is_null($result)) {
                return redirect()->back()->withErrors([
                    'domain' => 'An unexpected error occurred while deleting the domain.',
                ]);
            }

            return redirect()->back()->with('success', 'Domain and related links successfully deleted.');
        }
        catch(Exception $e){
            $this->logError("Error deleting domain", $e);
            return redirect()->back()->withErrors([
                'domain' => 'An internal server error occurred. Please try again later.',
            ]);
        }
    }
}
