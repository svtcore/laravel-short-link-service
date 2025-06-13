<?php

namespace Tests\Unit\Services;

use App\Http\Services\LinkService;
use App\Models\Link;
use App\Models\Domain;
use App\Models\User;
use App\Models\LinkHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class LinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private LinkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LinkService();
    }

    public function test_store_link(): void
    {
        $domain = Domain::factory()->create();
        $user = User::factory()->create();

        $result = $this->service->storeLink(
            'https://example.com',
            $domain,
            'test123',
            'custom-name',
            $user->id,
            '192.168.1.1'
        );

        $this->assertNotNull($result);
        $this->assertEquals('test123', $result['short_name']);
        $this->assertEquals($domain->name, $result['domain']['name']);
        $this->assertDatabaseHas('links', [
            'short_name' => 'test123',
            'domain_id' => $domain->id,
            'user_id' => $user->id
        ]);
    }

    public function test_store_link_failure(): void
    {
        $domain = new \stdClass();
        $domain->id = 999; // Non-existent domain

        $result = $this->service->storeLink(
            'https://example.com',
            $domain,
            'test123',
            'custom-name',
            1,
            '192.168.1.1'
        );

        $this->assertNull($result);
    }

    public function test_generate_short_path(): void
    {
        $path = $this->service->generateShortPath();
        $this->assertNotNull($path);
        $this->assertEquals(7, strlen($path));
    }

    public function test_generate_short_name(): void
    {
        Domain::factory()->create();
        $result = $this->service->generateShortName(
            'https://example.com',
            null,
            null,
            '192.168.1.1'
        );
        $this->assertArrayHasKey('short_name', $result);
        $this->assertArrayHasKey('domain', $result);
        $this->assertNotNull($result);
    }

    public function test_get_total_user_links(): void
    {
        $user = User::factory()->create();
        Link::factory()->count(3)->create(['user_id' => $user->id]);

        $count = $this->service->getTotalUserLinks($user->id);
        $this->assertEquals(3, $count);
    }

    public function test_get_user_links_data(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create();
        $link = Link::factory()->create([
            'user_id' => $user->id,
            'domain_id' => $domain->id
        ]);
        LinkHistory::factory()->create(['link_id' => $link->id]);

        $data = $this->service->getUserLinksData($user->id);
        $this->assertNotNull($data);
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]->unique_clicks);
        $this->assertEquals(1, $data[0]->total_clicks);
    }

    public function test_is_own_user(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);

        $result = $this->service->isOwnUser($link->id, $user->id);
        $this->assertTrue($result);

        $result = $this->service->isOwnUser($link->id, 999);
        $this->assertFalse($result);
    }

    public function test_get_by_id(): void
    {
        $link = Link::factory()->create();
        $found = $this->service->getById($link->id);
        $this->assertNotNull($found);
        $this->assertEquals($link->id, $found->id);
    }

    public function test_update_link(): void
    {
        $link = Link::factory()->create();
        $updated = $this->service->updateLink(
            'new-name',
            'https://new.example.com',
            false,
            $link->id
        );

        $this->assertEquals(1, $updated);
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'custom_name' => 'new-name',
            'destination' => 'https://new.example.com',
            'available' => false
        ]);
    }

    public function test_destroy_link(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->create(['link_id' => $link->id]);

        $result = $this->service->destroyLink($link->id);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('links', ['id' => $link->id]);
        $this->assertDatabaseMissing('link_histories', ['link_id' => $link->id]);
    }

    public function test_get_links_list(): void
    {
        Link::factory()->count(5)->create();
        $links = $this->service->getLinksList();
        $this->assertNotNull($links);
        $this->assertCount(5, $links);
    }

    public function test_search_links(): void
    {
        $domain = Domain::factory()->create(['name' => 'example.com']);
        $link = Link::factory()->create([
            'domain_id' => $domain->id,
            'short_name' => 'test123',
            'destination' => 'https://destination.com'
        ]);

        // Search by domain
        $results = $this->service->searchLinks('example.com', false);
        $this->assertNotNull($results);
        $this->assertCount(1, $results);

        // Search by short name
        $results = $this->service->searchLinks('test123', false);
        $this->assertCount(1, $results);

        // Search by destination
        $results = $this->service->searchLinks('destination.com', false);
        $this->assertCount(1, $results);

        // Count only
        $count = $this->service->searchLinks('example.com', true);
        $this->assertEquals(1, $count);
    }

    public function test_search_by_domain_id(): void
    {
        $domain = Domain::factory()->create();
        Link::factory()->create(['domain_id' => $domain->id]);

        $results = $this->service->searchByDomainId($domain->id);
        $this->assertNotNull($results);
        $this->assertCount(1, $results);
    }

    public function test_search_by_user_ip(): void
    {
        Link::factory()->create(['ip_address' => '192.168.1.1']);
        $results = $this->service->searchByUserIP('192.168.1.1');
        $this->assertNotNull($results);
        $this->assertCount(1, $results);
    }

    public function test_error_handling_in_get_total_user_links(): void
    {
        DB::shouldReceive('table')->andThrow(new \Exception('Database error'));
        $result = $this->service->getTotalUserLinks(1);
        $this->assertFalse(false, $result);
    }

    public function test_empty_result_in_search_links(): void
    {
        $results = $this->service->searchLinks('nonexistent', false);
        $this->assertNotNull($results);
        $this->assertCount(0, $results);
    }
}
