<?php

namespace Tests\Unit\Controllers\User;

use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Controllers\User\DashboardController;
use App\Models\Domain;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private DashboardController $controller;

    private User $user;

    private $linkService;

    private $linkHistoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->linkService = $this->createMock(LinkServiceInterface::class);
        $this->linkHistoryService = $this->createMock(LinkHistoryServiceInterface::class);
        $this->controller = new DashboardController($this->linkService, $this->linkHistoryService);

        Role::firstOrCreate(['name' => 'user']);
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->assignRole('user');
    }

    public function test_index_returns_expected_stats(): void
    {
        $this->actingAs($this->user);

        // Create test data
        $domain = Domain::factory()->create();
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
        ]);
        LinkHistory::factory()->count(5)->create([
            'link_id' => $link->id,
        ]);

        $this->linkService->method('getTotalUserLinks')
            ->willReturn(1);
        $this->linkHistoryService->method('getTotalClicksByUserId')
            ->willReturn(5);

        $response = $this->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
        $response->assertViewHas('links_count', 1);
        $response->assertViewHas('clicks_count', 5);
    }

    public function test_index_handles_invalid_user(): void
    {
        $invalidUser = User::factory()->create();
        $invalidUser->delete();
        $invalidUser->assignRole('user');

        $response = $this->actingAs($invalidUser)
            ->get(route('user.dashboard'));

        $response->assertStatus(403);
    }

    public function test_index_returns_empty_stats_for_new_user(): void
    {
        $newUser = User::factory()->create();
        $newUser->assignRole('user');

        $this->linkService->method('getTotalUserLinks')
            ->willReturn(0);
        $this->linkHistoryService->method('getTotalClicksByUserId')
            ->willReturn(0);

        $response = $this->actingAs($newUser)
            ->get(route('user.dashboard'));

        $response->assertViewHas('links_count', 0);
        $response->assertViewHas('clicks_count', 0);
    }
}
