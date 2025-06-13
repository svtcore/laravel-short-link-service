<?php

namespace Tests\Unit\Services;

use App\Http\Services\LinkHistoryService;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use App\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class LinkHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private LinkHistoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LinkHistoryService();
    }

    public function test_get_total_clicks_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        LinkHistory::factory()->count(3)->create(['link_id' => $link->id]);

        $result = $this->service->getTotalClicksByUserId($user->id);
        $this->assertEquals(3, $result);
    }

    public function test_get_total_clicks_by_link_id(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->count(2)->create(['link_id' => $link->id]);
        LinkHistory::factory()->create(['link_id' => $link->id, 
            'created_at' => Carbon::now()->subDays(2)]);
        LinkHistory::factory()->create(['link_id' => $link->id, 
            'created_at' => Carbon::now()->addDays(2)]);

        $result = $this->service->getTotalClicksByLinkId(
            $link->id,
            Carbon::now()->subDay()->format('Y-m-d'),
            Carbon::now()->addDay()->format('Y-m-d')
        );
        $this->assertEquals(2, $result);
    }

    public function test_get_unique_ips_by_link_id(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.1']);

        $result = $this->service->getUniqueIpsByLinkId(
            $link->id,
            Carbon::now()->subDay()->format('Y-m-d'),
            Carbon::now()->addDay()->format('Y-m-d')
        );
        $this->assertEquals(2, $result);
    }

    public function test_get_unique_ips_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.1']);

        $result = $this->service->getUniqueIpsByUserId($user->id);
        $this->assertEquals(2, $result);
    }

    public function test_get_today_total_clicks_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        LinkHistory::factory()->count(2)->create(['link_id' => $link->id]);
        LinkHistory::factory()->create(['link_id' => $link->id, 
            'created_at' => Carbon::yesterday()]);

        $result = $this->service->getTodayTotalClicksByUserId($user->id);
        $this->assertEquals(2, $result);
    }

    public function test_get_top_links_clicks_by_user_id(): void
    {
        $user = User::factory()->create();
        $link1 = Link::factory()->create(['user_id' => $user->id, 'destination' => 'https://example1.com']);
        $link2 = Link::factory()->create(['user_id' => $user->id, 'destination' => 'https://example2.com']);
        
        LinkHistory::factory()->count(3)->create(['link_id' => $link1->id]);
        LinkHistory::factory()->count(5)->create(['link_id' => $link2->id]);

        $result = $this->service->getTopLinksClicksByUserId($user->id);
        $this->assertCount(2, $result);
        $this->assertEquals('https://example2.com', $result[0]['url']);
        $this->assertEquals(5, $result[0]['click_count']);
    }

    public function test_get_top_countries_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'Ukraine', 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'Ukraine', 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'USA', 'ip_address' => '192.168.2.1']);

        $result = $this->service->getTopCountriesByUserId($user->id);
        $this->assertCount(2, $result);
        $this->assertEquals('Ukraine', $result[0]['country']);
        $this->assertEquals(2, $result[0]['click_count']);
    }

    public function test_get_top_browsers_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'browser' => 'Chrome', 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'browser' => 'Chrome', 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'browser' => 'Firefox', 'ip_address' => '192.168.2.1']);

        $result = $this->service->getTopBrowsersByUserId($user->id);
        $this->assertCount(2, $result);
        $this->assertEquals('Chrome', $result[0]['browser']);
        $this->assertEquals(2, $result[0]['click_count']);
    }

    public function test_get_top_operating_systems_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'os' => 'Windows', 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'os' => 'Windows', 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'os' => 'macOS', 'ip_address' => '192.168.2.1']);

        $result = $this->service->getTopOperatingSystemsByUserId($user->id);
        $this->assertCount(2, $result);
        $this->assertEquals('Windows', $result[0]['os']);
        $this->assertEquals(2, $result[0]['click_count']);
    }

    public function test_get_hourly_clicks_by_user_id(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(10)]);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(10)]);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(15)]);

        $result = $this->service->getHourlyClicksByUserId($user->id);
        $this->assertEquals(2, $result[10]);
        $this->assertEquals(1, $result[15]);
        $this->assertEquals(0, $result[0]);
    }

    public function test_get_hourly_clicks_by_link_id(): void
    {
        $link = Link::factory()->create();
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(10)]);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(10)]);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => Carbon::today()->setHour(15)]);

        $result = $this->service->getHourlyClicksByLinkId(
            $link->id,
            Carbon::today()->format('Y-m-d'),
            Carbon::today()->format('Y-m-d')
        );
        $this->assertEquals(2, $result[10]);
        $this->assertEquals(1, $result[15]);
        $this->assertEquals(0, $result[0]);
    }

    public function test_get_top_metrics_by_link_id(): void
    {
        $link = Link::factory()->create();
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'Ukraine', 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'Ukraine', 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'country_name' => 'USA', 'ip_address' => '192.168.2.1']);

        $result = $this->service->getTopMetricsByLinkId(
            $link->id,
            Carbon::today()->subDay()->format('Y-m-d'),
            Carbon::today()->addDay()->format('Y-m-d'),
            'country_name'
        );
        $this->assertCount(2, $result);
        $this->assertEquals('Ukraine', $result[0]['country_name']);
        $this->assertEquals(2, $result[0]['click_count']);
    }

    public function test_get_daily_clicks_by_link_id(): void
    {
        $link = Link::factory()->create();
        
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-02']);

        $result = $this->service->getDailyClicksByLinkId(
            $link->id,
            '2025-01-01',
            '2025-01-03'
        );
        $this->assertEquals(2, $result['2025-01-01']);
        $this->assertEquals(1, $result['2025-01-02']);
        $this->assertEquals(0, $result['2025-01-03']);
    }

    public function test_process_redirect(): void
    {
        $domain = Domain::factory()->create(['name' => 'mydomain.com', 'available' => true]);

        $link = Link::factory()->create([
            'domain_id' => $domain->id,
            'user_id' => null,
            'ip_address' => '195.0.0.0',
            'custom_name' => null,
            'short_name' => 'test123',
            'destination' => 'https://example.com',
            'created_at' => now(),
        ]);
        
        $result = $this->service->processRedirect([
            'host' => 'mydomain.com',
            'path' => 'test123',
            'ip' => '195.1.1.1',
            'user_agent' => 'Mozilla/5.0'
        ]);
        
        $this->assertEquals($link->destination, $result['link']);
        $this->assertDatabaseHas('link_histories', [
            'link_id' => $link->id,
            'ip_address' => '195.1.1.1'
        ]);
    }

    public function test_empty_result_in_get_daily_clicks(): void
    {
        $result = $this->service->getDailyClicksByLinkId(
            1,
            '2025-01-01',
            '2025-01-03'
        );
        
        $expected = [
            '2025-01-01' => 0,
            '2025-01-02' => 0,
            '2025-01-03' => 0
        ];
        
        $this->assertEquals($expected, $result);
    }
}
