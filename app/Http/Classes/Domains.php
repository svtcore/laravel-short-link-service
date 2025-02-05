<?php

namespace App\Http\Classes;

use App\Models\Domain;
use Exception;
use App\Http\Traits\LogsErrors;
use App\Models\LinkHistory;
use Illuminate\Support\Facades\DB;

class Domains
{
    use LogsErrors;
    /**
     * Retrieve a random available domain from the database.
     *
     * @return Domain|null
     */
    public function getRandomDomain(): ?Domain
    {
        try {
            // Fetch a random domain that is marked as available
            return Domain::where('available', true)
                ->inRandomOrder()
                ->first();
        } catch (Exception $e) {
            // Log the exception details
            $this->logError('Error occurred while retrieving the domain.', $e);
            return null;
        }
    }


    public function getDomainsList(?int $count): ?iterable
    {
        try {
            $domains = Domain::select('id', 'name', 'available', 'created_at')
                ->withCount('links')
                ->with(['links' => function ($query) {
                    $query->select('id', 'domain_id')
                        ->withCount('link_histories');
                }])
                ->get();

            foreach ($domains as $domain) {
                $domain->total_link_histories = $domain->links->sum('link_histories_count');
            }

            $sortedDomains = $domains->sortByDesc('links_count');

            return $count !== null ? $sortedDomains->take($count) : $sortedDomains;
        } catch (Exception $e) {
            $this->logError("Error fetching top used domains", $e);
            return null;
        }
    }

    public function storeDomain(string $name, bool $status): ?bool
    {
        try {
            $domain = Domain::create([
                'name' => $name,
                'available' => $status,
            ]);

            return $domain !== null;
        } catch (Exception $e) {
            $this->logError("Error storing domain", $e, [
                'name' => $name,
                'status' => $status,
            ]);
            return null;
        }
    }

    public function updateDomain(array $data): ?bool
    {
        try {
            $domain = Domain::findOrFail($data['id']);
            $result = $domain->update([
                'name' => $data['domainName'],
                'available' => $data['domainStatus'],
            ]);
            return $result !== null ? true : false;
        } catch (Exception $e) {
            $this->logError("Error while update domain", $e);
            return null;
        }
    }

    public function destroyDomain($id): ?bool
    {
        DB::beginTransaction();
        try {
            $domain = Domain::findOrFail($id);
            $result = $domain->delete();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error while deleting domain", $e, ['id' => $id]);
            return null;
        }
    }
}
