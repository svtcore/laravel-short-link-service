<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Domain;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $config = [
            'users' => 50,
            'links' => 100,
            'domains' => 10,
            'link_history' => 2000,
        ];

        // Creating roles in a loop to avoid repetition
        $roles_list = ['admin', 'user'];
        foreach ($roles_list as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Creating specific users with roles
        $admin = $this->createUser('John Doe', 'admin@admin.com', 'admin', Hash::make("password"));
        $admin->assignRole('user');
        $this->createUser('Test user', 'user@user.com', 'user', Hash::make("password"));

        // Generating random users and assigning roles
        for ($i = 0; $i < $config['users']; $i++) {
            $randRole = $roles_list[array_rand($roles_list)];
            $this->createUser(null, null, $randRole);
        }

        // Creating domains
        Domain::factory()->count($config['domains'])->create();

        Link::factory($config['links'])->create([
            'user_id' => rand(1, $config['users']),
            'domain_id' => rand(1, $config['domains']),
        ]);
        // Create link histories with randomized dates
        $daysRange = 30;
        
        for ($i = 1; $i <= $config['links']; $i++) {
            $entriesPerLink = rand(1, $config['link_history']);
            $interval = $daysRange / $entriesPerLink;
            for ($j = 1; $j <= $entriesPerLink; $j++) {
                $daysAgo = (int) round($j * $interval);
                $randomDate = now()->subDays($daysAgo);
                
                LinkHistory::factory()->create([
                    'link_id' => $i,
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            }
        }
        // Creating links and link histories
        for ($i = 1; $i <= $config['links']; $i++) {
            $link = Link::factory()->create([
                'user_id' => rand(1, $config['users']),
                'domain_id' => rand(1, $config['domains']),
            ]);
            $this->createLinkHistory($link, $config['link_history']);
        }
    }

    /**
     * Helper function to create a user with a specific role.
     */
    private function createUser(string $name = null, string $email = null, string $role = 'user', string $password = null): User
    {
        if ($password == null) $password = Hash::make(Str::random(16));
        $user = User::factory()->create([
            'name' => $name ?? fake()->name(),
            'email' => $email ?? fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => $password,
            'remember_token' => Str::random(60),
        ]);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Helper function to create link history entries with realistic behavior.
     */
    private function createLinkHistory(Link $link, int $count): void
    {
        $faker = Factory::create();
        $totalEntries = rand(1, $count * 2); // Random number of entries with bigger range
        $daysRange = 30;
        
        // Create pool of IPs (some will repeat)
        $ipPool = array_map(function() use ($faker) {
            return $faker->ipv4();
        }, range(1, max(5, $totalEntries/10)));

        $interval = $daysRange / max(1, $totalEntries);
        
        for ($j = 1; $j <= $totalEntries; $j++) {
            $daysAgo = (int) round($j * $interval);
            // Create random time between 00:00:00 and 23:59:59
            $randomHours = rand(0, 23);
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);
            $randomDate = now()
                ->subDays($daysAgo)
                ->setTime($randomHours, $randomMinutes, $randomSeconds);
            
            // 20% chance to reuse IP from pool
            $ip = (rand(1, 5) === 1 && !empty($ipPool)) 
                ? $ipPool[array_rand($ipPool)]
                : $faker->ipv4();

            LinkHistory::factory()->create([
                'link_id' => $link->id,
                'ip_address' => $ip,
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }
    }
}
