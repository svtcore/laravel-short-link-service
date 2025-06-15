<?php

namespace Tests\Unit\Middleware;

use App\Models\Domain;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected $regularUser;

    protected $testDomain;

    protected $testLink;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create test users
        $this->adminUser = User::factory()->create()->assignRole('admin');
        $this->regularUser = User::factory()->create()->assignRole('user');

        // Create test data
        $this->testDomain = Domain::factory()->create();
        $this->testLink = Link::factory()->create(['domain_id' => Domain::factory()->create()->id, 'user_id' => $this->regularUser->id]);
    }

    // Test user routes access
    public function test_user_routes_access()
    {
        // Dashboard
        $this->actingAs($this->regularUser)
            ->get(route('user.dashboard'))
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->get(route('user.dashboard'))
            ->assertStatus(403);

        // Links
        $this->actingAs($this->regularUser)
            ->get(route('user.links.index'))
            ->assertStatus(200);

        $this->actingAs($this->regularUser)
            ->get(route('user.links.show', ['id' => $this->testLink->id]))
            ->assertStatus(200);

        $this->actingAs($this->regularUser)
            ->get(route('user.links.edit', $this->testLink->id))
            ->assertStatus(200);

        // Settings
        $this->actingAs($this->regularUser)
            ->get(route('user.settings.index'))
            ->assertStatus(200);
    }

    // Test admin routes access
    public function test_admin_routes_access()
    {
        // Dashboard
        $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);

        // Domains
        $this->actingAs($this->adminUser)
            ->get(route('admin.domains.index'))
            ->assertStatus(200);

        // Links
        $this->actingAs($this->adminUser)
            ->get(route('admin.links.index'))
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->get(route('admin.links.show', $this->testLink->id))
            ->assertStatus(200);

        // Users
        $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'))
            ->assertStatus(200);

        // Settings
        $this->actingAs($this->adminUser)
            ->get(route('admin.settings.index'))
            ->assertStatus(200);

        // Search
        $this->actingAs($this->adminUser)
            ->get(route('admin.search.count', ['query' => 'test']))
            ->assertStatus(200);
    }

    // Test unauthorized access
    public function test_unauthorized_access()
    {
        // User trying to access admin routes
        $this->actingAs($this->regularUser)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        // Admin trying to access user-specific routes (should be allowed if needed)
        $this->actingAs($this->adminUser)
            ->get(route('user.dashboard'))
            ->assertStatus(403);
    }

    // Test guest access
    public function test_guest_access()
    {
        $this->get(route('user.dashboard'))->assertStatus(403);

        $this->get(route('admin.dashboard'))->assertStatus(403);
    }
}
