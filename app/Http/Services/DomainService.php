<?php

namespace App\Http\Services;


use App\Http\Contracts\Interfaces\DomainServiceInterface;
use App\Models\Domain;
use Exception;
use App\Http\Traits\LogsErrors;
use App\Models\LinkHistory;
use Illuminate\Support\Facades\DB;

class DomainService implements DomainServiceInterface
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


    /**
     * Get a list of domains with statistics about links and clicks.
     *
     * @param int|null $count Optional limit for number of domains to return
     * @return iterable|null Collection of domains with:
     *                      - id
     *                      - name 
     *                      - available status
     *                      - created_at
     *                      - links_count
     *                      - total_link_histories
     * @throws Exception On database query failure
     */
    public function getDomainsList(?int $count): ?iterable
    {
        try {
            $domains = Domain::select('id', 'name', 'available', 'created_at')
                ->withCount('links')
                ->with([
                    'links' => function ($query) {
                        $query->select('id', 'domain_id')
                            ->withCount('link_histories');
                    }
                ])
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

    /**
     * Store a new domain in the database.
     *
     * @param string $name Domain name to store
     * @param bool $status Initial availability status
     * @return bool|null True if created successfully, false if failed, null on error
     * @throws Exception On database operation failure
     */
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

    /**
     * Update an existing domain's name and status.
     *
     * @param array $data {
     *     @var int $id Domain ID to update
     *     @var string $domainName New domain name
     *     @var bool $domainStatus New availability status
     * }
     * @return bool|null True if updated successfully, false if failed, null on error
     * @throws Exception On database operation failure
     */
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

    /**
     * Delete a domain from the database.
     *
     * @param int $id ID of domain to delete
     * @return bool|null True if deleted successfully, false if failed, null on error
     * @throws Exception On database operation failure
     */
    public function destroyDomain(int $id): ?bool
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

    /**
     * Search domains by name with optional count-only mode.
     *
     * @param string $query Search term to match against domain names
     * @param bool $count If true, returns only count of matches
     * @return mixed Collection of matching domains if $count=false, 
     *              integer count if $count=true,
     *              null on error
     * @throws Exception On database query failure
     */
    public function searchDomains(string $query, bool $count = false): mixed
    {
        try {
            $query = Domain::query()
                ->where('name', 'LIKE', '%' . $query . '%')
                ->withCount([
                    'links',
                    'links as total_clicks' => function ($q) {
                        $q->withCount('link_histories');
                    }
                ]);

            return $count ? $query->count() : $query->get();

        } catch (Exception $e) {
            $this->logError("Domain search failed for query: {$query}", $e);
            return $count ? 0 : collect();
        }
    }
}
