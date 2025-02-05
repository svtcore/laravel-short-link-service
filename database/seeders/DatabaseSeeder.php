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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $config = [
            'users' => 50,
            'links' => 100000,
            'domains' => 10,
            'link_history' => 100,
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
     * Helper function to create link history entries for a link.
     */
    private function createLinkHistory(Link $link, int $count): void
    {
        for ($j = 1; $j <= rand(1, $count); $j++) {
            LinkHistory::factory()->create([
                'link_id' => $link->id,
            ]);
        }
    }
}
