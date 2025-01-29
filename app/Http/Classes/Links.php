<?php

namespace App\Http\Classes;

use App\Models\Link;
use Exception;
use App\Http\Classes\Users;
use App\Models\LinkHistory;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use App\Http\Traits\LogsErrors;

class Links extends Domains
{
    use LogsErrors;
    /**
     * Store a new short link in the database.
     *
     * This method creates a new short link record, associates it with a domain,
     * and returns the data in the specified JSON format.
     *
     * @param string $url The destination URL.
     * @param object $randomDomain The randomly selected domain object.
     * @param string $shortPath The generated short path.
     * @param string|null $custom_name An optional custom name for the link.
     * @param int|null $user_id The ID of the user creating the link (nullable for guests).
     * @return array|null The link data in the required format or null if creation failed.
     */
    public function store(string $url, object $randomDomain, string $shortPath, ?string $custom_name, ?int $user_id): ?array
    {
        DB::beginTransaction();

        try {
            $newLink = Link::create([
                'user_id' => $user_id,
                'domain_id' => $randomDomain->id,
                'custom_name' => $custom_name,
                'destination' => $url,
                'short_name' => $shortPath,
                'available' => true,
            ]);

            // Check if the record was successfully created
            if (!$newLink || !$newLink->id) {
                return null;
            }

            DB::commit();
            // Return the link data in the required format
            return [
                'short_name' => $newLink->short_name,
                'domain' => [
                    'name' => $randomDomain->name,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error storing new short link", $e, [
                'url' => $url,
                'randomDomain' => $randomDomain,
                'shortPath' => $shortPath,
                'custom_name' => $custom_name,
                'user_id' => $user_id,
            ]);
            return null;
        }
    }


    /**
     * Generate a short link for a given URL.
     *
     * This method creates a unique short link by combining a random domain and 
     * a generated short path. It ensures security and handles errors gracefully.
     *
     * @param string $url The destination URL.
     * @param string|null $custom_name An optional custom name for the link.
     * @param int|null $user_id The ID of the user creating the link (nullable for guests).
     * @return array|null The generated short link in the required format or null on failure.
     */
    public function generateShortName(string $url, ?string $custom_name, ?int $user_id): ?array
    {
        DB::beginTransaction();
        try {
            $randomDomain = $this->getRandomDomain();
            $shortPath = $this->generateShortPath();

            if ($randomDomain != null && $shortPath != null) {
                //check if both parts already exist
                $isDuplicate = Link::where('domain_id', $randomDomain->id)
                    ->where('short_name', $shortPath)
                    ->exists();
                if (!$isDuplicate) {
                    DB::commit();
                    return $this->store($url, $randomDomain, $shortPath, $custom_name, $user_id);
                }
            } else {
                DB::rollBack();
                return null;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error generating short name", $e, [
                'url' => $url,
                'custom_name' => $custom_name,
                'user_id' => $user_id,
            ]);
            return null;
        }
    }

    /**
     * Generate a secure, random short URL path.
     *
     * This method generates a cryptographically secure alphanumeric string of a specified length.
     * The default length is 7 characters. The generated path is suitable for use in short URLs.
     *
     * @param int $length Length of the short URL path (minimum: 1, default: 7).
     * @return string|null The generated short URL path, or null on failure.
     */
    public function generateShortPath(int $length = 7): ?string
    {
        try {
            // Define the character set
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $charactersLength = strlen($characters);

            // Generate a random path
            $shortPath = '';
            for ($i = 0; $i < $length; $i++) {
                $shortPath .= $characters[random_int(0, $charactersLength - 1)];
            }

            return $shortPath;
        } catch (Exception $e) {
            $this->logError("Error generating short path", $e, ['length' => $length]);
            return null;
        }
    }


    /**
     * Get the total number of links created by a specific user.
     *
     * This method retrieves the count of links associated with a given user ID from the database.
     * It ensures proper exception handling and logging in case of errors.
     *
     * @param int $user_id The ID of the user.
     * @return int|null The total number of links, or null if an error occurs.
     */
    public function getTotalUserLinks(int $user_id): ?int
    {
        try {
            return Link::where('user_id', $user_id)->count();
        } catch (Exception $e) {
            $this->logError("Failed to fetch the total number of user links", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Fetch detailed data for links created by a specific user.
     *
     * This method retrieves all links created by the user, including associated domains,
     * unique clicks, and total clicks for each link.
     *
     * @param int|null $user_id The ID of the user for whom to fetch the link data.
     * @return \Illuminate\Support\Collection|null A collection of link data or null in case of an error.
     */
    public function getUserLinksData(?int $user_id): ?\Illuminate\Support\Collection
    {
        try {
            // Fetch links with associated domain and calculated click metrics
            return Link::with(['domain:id,name'])
                ->where('user_id', $user_id)
                ->addSelect([
                    'unique_clicks' => LinkHistory::selectRaw('COUNT(DISTINCT ip_address)')
                        ->whereColumn('link_histories.link_id', 'links.id'),
                    'total_clicks' => LinkHistory::selectRaw('COUNT(*)')
                        ->whereColumn('link_histories.link_id', 'links.id'),
                ])
                ->get()
                ->map(function ($link) {
                    // Hide unnecessary attributes for clean output
                    $link->makeHidden(['id', 'user_id', 'domain_id']);
                    if ($link->domain) {
                        $link->domain->makeHidden(['id']);
                    }
                    return $link;
                });
        } catch (Exception $e) {
            $this->logError("Error fetching user links data", $e, ['user_id' => $user_id]);
            return null;
        }
    }


    /**
     * Check if a link belongs to a specific user.
     *
     * This method verifies whether the given link ID is associated with the specified user ID.
     *
     * @param int|null $link_id The ID of the link.
     * @param int|null $user_id The ID of the user.
     * @return bool True if the link belongs to the user, false otherwise or if an error occurs.
     */
    public function isOwnUser(?int $link_id, ?int $user_id): bool
    {
        try {
            return Link::where('user_id', $user_id)
                ->where('id', $link_id)
                ->exists();
        } catch (Exception $e) {
            $this->logError("Error verifying ownership of link", $e, [
                'link_id' => $link_id,
                'user_id' => $user_id,
            ]);
            return false;
        }
    }


    /**
     * Retrieve a link by its ID.
     *
     * This method fetches a link along with its associated domain details using the provided ID.
     *
     * @param string $id The ID of the link to fetch.
     * @return object|null The link object with its domain, or null if not found or an error occurs.
     */
    public function getById(string $id): ?object
    {
        try {
            return Link::with('domain')
                ->where('id', $id)
                ->first();
        } catch (Exception $e) {
            $this->logError("Error fetching link by ID", $e, ['id' => $id]);
            return null;
        }
    }


    /**
     * Update a link's details by its ID.
     *
     * Updates the custom name, destination URL, and availability status of a link.
     * Returns the number of affected rows, or null if an error occurs.
     *
     * @param string|null $name The custom name of the link (nullable).
     * @param string $destination The destination URL for the link.
     * @param bool $availability The availability status of the link.
     * @param int $id The ID of the link to update.
     * @return int|null The number of affected rows, or null if an error occurs.
     */
    public function update(?string $name, string $destination, bool $availability, int $id): ?int
    {
        DB::beginTransaction();
        try {
            $result = Link::where('id', $id)->update([
                'custom_name' => $name,
                'destination' => $destination,
                'available' => $availability,
            ]);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError("Error updating link", $e, [
                'id' => $id,
                'name' => $name,
                'destination' => $destination,
            ]);
            return null;
        }
    }


    /**
     * Delete a link by its ID and all associated link histories.
     *
     * This method deletes the link specified by its ID and also removes all 
     * associated records in the `link_histories` table. Returns a boolean indicating 
     * the success or failure of the deletion.
     *
     * @param int $id The ID of the link to delete.
     * @return bool|null Returns true if the deletion was successful, false otherwise. 
     *                   Returns null in case of an error.
     */
    public function destroy(int $id): ?bool
    {
        DB::beginTransaction();

        try {
            $link = Link::findOrFail($id);
            $link->link_histories()->delete();
            $result = $link->delete();

            DB::commit(); //complete transaction

            return $result;
        } catch (Exception $e) {
            //if error rollback
            DB::rollBack();
            $this->logError("Error deleting link", $e, ['id' => $id]);
            return null;
        }
    }

    /**
     * Retrieve the link by its domain and short name.
     * 
     * This method searches for a link by matching the domain name and short name.
     * If no link is found, it returns null.
     *
     * @param string $domain The domain name associated with the link.
     * @param string $short_name The short name (or slug) of the link.
     * @return Link|null The link object if found, or null if no matching link exists.
     */
    protected function getLinkByDomainAndShortName(string $domain, string $short_name): ?Link
    {
        return Link::whereHas('domain', function ($query) use ($domain) {
            $query->where('name', $domain);
        })
        ->where('short_name', $short_name)
        ->where('available', true)
        ->first();
    }
}
