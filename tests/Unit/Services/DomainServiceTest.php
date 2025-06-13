<?php

namespace Tests\Unit\Services;

use App\Http\Services\DomainService;
use App\Models\Domain;
use App\Models\Link;
use App\Models\LinkHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainServiceTest extends TestCase
{
    use RefreshDatabase;

    private DomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DomainService();
    }

    public function test_get_random_domain(): void
    {
        Domain::factory()->count(5)->create(['available' => true]);
        Domain::factory()->count(3)->create(['available' => false]);

        $result = $this->service->getRandomDomain();

        $this->assertNotNull($result);
        $this->assertEquals(true, $result->available); //return 1 not true
        $this->assertInstanceOf(Domain::class, $result);
    }

    public function test_get_random_domain_returns_null_when_no_available(): void
    {
        Domain::factory()->count(3)->create(['available' => false]);
        
        $this->assertNull($this->service->getRandomDomain());
    }

    public function test_get_domains_list(): void
    {
        $domain = Domain::factory()->create();
        Link::factory()->count(2)->create(['domain_id' => $domain->id]);
        LinkHistory::factory()->count(3)->create(['link_id' => $domain->links->first()->id]);

        $result = $this->service->getDomainsList(null);
        
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result->first()->links_count);
        $this->assertEquals(3, $result->first()->total_link_histories);
    }

    public function test_get_domains_list_with_limit(): void
    {
        Domain::factory()->count(5)->create();
        
        $result = $this->service->getDomainsList(3);
        $this->assertCount(3, $result);
    }

    public function test_domains_list_sorted_by_links_count(): void
    {
        $domain1 = Domain::factory()->create();
        Link::factory()->count(3)->create(['domain_id' => $domain1->id]);
        
        $domain2 = Domain::factory()->create();
        Link::factory()->count(5)->create(['domain_id' => $domain2->id]);
        
        $result = $this->service->getDomainsList(null);
        $this->assertEquals($domain2->id, $result->first()->id);
    }

    public function test_store_domain(): void
    {
        $result = $this->service->storeDomain('example.com', true);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('domains', [
            'name' => 'example.com',
            'available' => true
        ]);
    }

    public function test_store_domain_handles_duplicate(): void
    {
        Domain::factory()->create(['name' => 'example.com']);
        
        $result = $this->service->storeDomain('example.com', true);
        $this->assertNull($result);
    }

    public function test_update_domain(): void
    {
        $domain = Domain::factory()->create(['name' => 'old.com', 'available' => false]);
        
        $result = $this->service->updateDomain([
            'id' => $domain->id,
            'domainName' => 'new.com',
            'domainStatus' => true
        ]);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'name' => 'new.com',
            'available' => true
        ]);
    }

    public function test_update_domain_fails_for_invalid_id(): void
    {
        $result = $this->service->updateDomain([
            'id' => 999,
            'domainName' => 'new.com',
            'domainStatus' => true
        ]);
        
        $this->assertNull($result);
    }

    public function test_destroy_domain(): void
    {
        $domain = Domain::factory()->create();
        
        $result = $this->service->destroyDomain($domain->id);

        $this->assertTrue($result);
    }

    public function test_search_domains(): void
    {
        Domain::factory()->create(['name' => 'example.com']);
        Domain::factory()->create(['name' => 'test.org']);
        
        $result = $this->service->searchDomains('example');
        $this->assertCount(1, $result);
        $this->assertEquals('example.com', $result->first()->name);
    }

    public function test_search_domains_count(): void
    {
        Domain::factory()->create(['name' => 'example.com']);
        Domain::factory()->create(['name' => 'example.org']);
        
        $result = $this->service->searchDomains('example', true);
        $this->assertEquals(2, $result);
    }

    public function test_error_handling_in_get_random_domain(): void
    {
        $mock = $this->mock(Domain::class);
        $mock->shouldReceive('where')->andThrow(new \Exception('Database error'));
        
        $this->assertNull($this->service->getRandomDomain());
    }

    public function test_error_handling_in_update_domain(): void
    {
        $mock = $this->mock(Domain::class);
        $mock->shouldReceive('findOrFail')->andThrow(new \Exception('Database error'));
        
        $this->assertNull($this->service->updateDomain([
            'id' => 1,
            'domainName' => 'test.com',
            'domainStatus' => true
        ]));
    }

    public function test_error_handling_in_destroy_domain(): void
    {
        $mock = $this->mock(Domain::class);
        $mock->shouldReceive('findOrFail')->andThrow(new \Exception('Database error'));
        
        $this->assertNull($this->service->destroyDomain(1));
    }
}
