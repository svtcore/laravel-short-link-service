<?php

namespace App\Http\Classes;

use App\Models\Domain;
use Exception;
use App\Http\Traits\LogsErrors;

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
}
