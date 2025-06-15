<?php

namespace App\Http\Contracts\Interfaces;

use App\Models\Domain;

interface DomainServiceInterface
{
    /**
     * Retrieve a random available domain
     *
     * @return Domain|null Returns random available domain or null
     */
    public function getRandomDomain(): ?Domain;

    /**
     * Get domains list with statistics
     *
     * @param  int|null  $count  Optional limit for results
     * @return iterable|null Collection of domains with stats or null on error
     */
    public function getDomainsList(?int $count): ?iterable;

    /**
     * Create new domain
     *
     * @param  string  $name  Domain name
     * @param  bool  $status  Availability status
     * @return bool|null Creation status or null on error
     */
    public function storeDomain(string $name, bool $status): ?bool;

    /**
     * Update domain data
     *
     * @param  array  $data  Domain update data
     * @return bool|null Update status or null on error
     */
    public function updateDomain(array $data): ?bool;

    /**
     * Delete domain
     *
     * @param  int  $id  Domain ID
     * @return bool|null Delete status or null on error
     */
    public function destroyDomain(int $id): ?bool;

    /**
     * Search domains
     *
     * @param  string  $query  Search term
     * @param  bool  $count  Flag to return count only
     * @return mixed Search results (collection or count)
     */
    public function searchDomains(string $query, bool $count = false): mixed;
}
