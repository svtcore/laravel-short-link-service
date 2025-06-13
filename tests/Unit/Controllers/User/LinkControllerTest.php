<?php

namespace Tests\Unit\Controllers\User;

use Tests\TestCase;
use App\Models\Link;
use App\Models\Domain;
use App\Models\LinkHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\User\LinkController;
use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Models\User;
use Spatie\Permission\Models\Role;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private LinkController $controller;
    private User $user;
    private $linkService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkService = $this->createMock(LinkServiceInterface::class);
        $mockLinkHistoryService = $this->createMock(LinkHistoryServiceInterface::class);
        $this->controller = new LinkController($this->linkService, $mockLinkHistoryService);

        Role::firstOrCreate(['name' => 'user']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    public function test_get_user_links_returns_view()
    {
        $mockCollection = new \Illuminate\Support\Collection([]);
        $this->linkService->method('getUserLinksData')
            ->willReturn($mockCollection);

        $response = $this->actingAs($this->user)
            ->get(route('user.links.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('user.links');
        $response->assertViewHas('links');
    }

    public function test_store_new_link_redirects_with_success()
    {
        $domain = Domain::factory()->create();
        
        $this->linkService->method('generateShortName')
            ->willReturn([
                'short_name' => 'abc123',
                'destination_url' => 'https://example.com'
            ]);
        $this->linkService->method('storeLink')
            ->willReturn([
                'id' => 1,
                'short_name' => 'abc123',
                'destination_url' => 'https://example.com'
            ]);

        $response = $this->actingAs($this->user)
            ->post(route('links.store'), [
                'url' => 'https://example.com',
                'from_modal' => true,
                'ip' => '127.0.0.1'
            ]);

        $response->assertRedirect(route('user.links.index'));
        $response->assertSessionHas('success');
    }

    public function test_update_user_link()
    {
        $this->actingAs($this->user);
        $domain = Domain::factory()->create();
        
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
            'short_name' => 'nameold',
            'custom_name' => 'old-name',
            'destination' => 'old.com',
            'available' => true,
        ]);

        $this->linkService->method('isOwnUser')
            ->willReturn(true);
        $this->linkService->method('updateLink')
            ->willReturn(1); // Number of affected rows

        $response = $this->putJson(
            route('user.links.update', $link->id),
            [
                'id' => $link->id,
                'custom_name' => 'new-name',
                'destination' => 'https://new.com',
                'access' => true
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Link updated successfully.'
        ]);
    }

    public function test_delete_user_link()
    {
        $this->actingAs($this->user);
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(
            route('user.links.destroy', $link->id),
            ['id' => $link->id]
        );

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
        $response->assertStatus(302);
    }

    public function test_cannot_access_other_users_links()
    {
        $otherUser = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user);
        
        $response = $this->getJson(route('user.links.show', [
            'id' => $link->id,
            'startDate' => null,
            'endDate' => null
        ]));
        $response->assertForbidden();
    }
}
