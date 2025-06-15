<?php

namespace Tests\Unit\Controllers\Admin;

use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Services\LinkService;
use App\Models\Domain;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private LinkService $service;

    private LinkController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LinkService;
        $mockLinkHistoryService = $this->createMock(LinkHistoryServiceInterface::class);
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $this->controller = new LinkController($this->service, $mockLinkHistoryService, $mockUserService);

        // Setup roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        // Create test admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
    }

    public function test_get_list_of_links()
    {
        $this->actingAs(User::first());
        $domain = Domain::factory()->create();
        $link = Link::factory()->create(['domain_id' => $domain->id]);
        LinkHistory::factory()->count(3)->create(['link_id' => $link->id]);

        $response = $this->controller->index();

        $this->assertArrayHasKey('links', $response->getData());
        $this->assertNotEmpty($response->getData()['links']);
    }

    public function test_store_new_link()
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create();

        $this->actingAs(User::first());

        $response = $this->post(
            route('admin.links.store'),
            [
                'user_email' => $user->email,
                'custom_name' => null,
                'url' => 'https://example.com',
            ],
            ['REMOTE_ADDR' => '195.1.1.1']
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Link successfully shortened');

        $this->assertDatabaseHas('links', [
            'user_id' => $user->id,
            'destination' => 'https://example.com',
            'ip_address' => '195.1.1.1',
        ]);
    }

    public function test_update_link()
    {
        $this->actingAs(User::first());

        $link = Link::factory()->create([
            'user_id' => User::first()->id,
            'domain_id' => Domain::factory()->create()->id,
            'short_name' => 'nameold',
            'custom_name' => 'old-name',
            'destination' => 'old.com',
            'available' => true,
            'created_at' => now(),
        ]);

        $response = $this->put(
            route('admin.links.update', $link->id),
            [
                'editCustomName' => 'new-name',
                'editURL' => 'https://new.com',
                'editStatus' => true,
            ]
        );

        $response->assertStatus(302);

        $response->assertSessionHas('success', 'Link successfully updated');

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'custom_name' => 'new-name',
            'destination' => 'https://new.com',
            'available' => true,
        ]);

    }

    public function test_delete_link()
    {
        $this->actingAs(User::first());
        $link = Link::factory()->create();

        $response = $this->delete(
            route('admin.links.destroy', $link->id),
            ['id' => $link->id]
        );

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_handle_errors_when_fetching_links()
    {
        $mockService = $this->createMock(LinkService::class);
        $mockService->method('getLinksList')->willThrowException(new \Exception);

        $mockLinkHistoryService = $this->createMock(LinkHistoryServiceInterface::class);
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $controller = new LinkController($mockService, $mockLinkHistoryService, $mockUserService);
        $response = $controller->index();

        $this->assertArrayHasKey('error', $response->getData());
    }
}
