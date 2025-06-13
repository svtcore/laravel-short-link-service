<?php

namespace Tests\Unit\Services;

use App\Http\Services\AdminStatisticsService;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AdminStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminStatisticsService $service;
    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdminStatisticsService();
        $this->faker = Factory::create();
    }

    public function test_get_total_links(): void
    {
        Link::factory()->count(5)->create(['available' => true]);
        Link::factory()->count(3)->create(['available' => false]);

        $this->assertEquals(8, $this->service->getTotalLinks());
        $this->assertEquals(5, $this->service->getTotalLinks(true));
        $this->assertEquals(3, $this->service->getTotalLinks(false));
    }

    public function test_get_total_active_links(): void
    {
        Link::factory()->count(4)->create(['available' => true]);
        Link::factory()->count(2)->create(['available' => false]);

        $this->assertEquals(4, $this->service->getTotalActiveLinks());
    }

    public function test_get_total_users(): void
    {
        User::factory()->count(7)->create();
        $this->assertEquals(7, $this->service->getTotalUsers());
    }

    public function test_get_total_clicks(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->count(5)->create(['link_id' => $link->id]);
        $this->assertEquals(5, $this->service->getTotalClicks());
    }

    public function test_get_total_unique_clicks(): void
    {
        $link = Link::factory()->create();

        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.1']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.2']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'ip_address' => '192.168.1.3']);
        LinkHistory::factory()->count(2)->create([
            'link_id' => $link->id,
            'ip_address' => '192.168.1.4'
        ]);

        $this->assertEquals(4, $this->service->getTotalUniqueClicks());
    }

    public function test_get_avg_clicks_per_link(): void
    {
        $link1 = Link::factory()->create();
        $link2 = Link::factory()->create();

        LinkHistory::factory()->count(3)->create(['link_id' => $link1->id]);
        LinkHistory::factory()->create(['link_id' => $link2->id]);

        $this->assertEquals(2, $this->service->getAvgClicksPerLink());
    }

    public function test_get_total_links_by_date(): void
    {
        Link::factory()->create(['created_at' => '2025-01-01']);
        Link::factory()->create(['created_at' => '2025-01-02']);
        Link::factory()->create(['created_at' => '2025-01-03']);
        Link::factory()->create(['created_at' => '2025-01-10']);

        $this->assertEquals(3, $this->service->getTotalLinksByDate('2025-01-01', '2025-01-05'));
    }

    public function test_get_total_clicks_by_date(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 10:00:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-02 11:00:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-10 12:00:00']);

        $this->assertEquals(2, $this->service->getTotalClicksByDate('2025-01-01', '2025-01-05'));
    }

    public function test_get_daily_clicks_by_date(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 10:00:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 11:00:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-02 12:00:00']);

        $result = $this->service->getDailyClicksByDate('2025-01-01', '2025-01-03');

        $expected = [
            '2025-01-01' => 2,
            '2025-01-02' => 1,
            '2025-01-03' => 0
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_get_hourly_clicks_by_date(): void
    {
        $link = Link::factory()->create();
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 10:00:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 10:30:00']);
        LinkHistory::factory()->create(['link_id' => $link->id, 'created_at' => '2025-01-01 15:00:00']);

        $result = $this->service->getHourlyClicksByDate('2025-01-01', '2025-01-01');

        $this->assertEquals(2, $result[10]);
        $this->assertEquals(1, $result[15]);
        $this->assertEquals(0, $result[0]);
    }

    public function test_get_top_countries_by_date(): void
    {
        $link = Link::factory()->create();
        $now = now();

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'Ukraine',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'Ukraine',
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Firefox',
            'os' => 'Linux',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'Ukraine',
            'ip_address' => '192.168.1.3',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Safari',
            'os' => 'macOS',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.1',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.2',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Chrome',
            'os' => 'Android',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');

        $endDate = Carbon::now()->addDay()->format('Y-m-d');

        $result = $this->service->getTopCountriesByDate($startDate, $endDate);

        $this->assertCount(2, $result);
        $this->assertEquals('Ukraine', $result[0]['country']);
        $this->assertEquals(3, $result[0]['click_count']);
        $this->assertEquals('USA', $result[1]['country']);
        $this->assertEquals(2, $result[1]['click_count']);
    }

    public function test_get_top_browsers_by_date(): void
    {
        $link = Link::factory()->create();
        $now = now();


        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.1',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.2',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.3',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.5',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Firefox',
            'os' => 'Android',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.6',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Firefox',
            'os' => 'Android',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');

        $endDate = Carbon::now()->addDay()->format('Y-m-d');

        $result = $this->service->getTopBrowsersByDate($startDate, $endDate);

        $this->assertNotNull($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Edge', $result[0]['browser']);
        $this->assertEquals(3, $result[0]['click_count']);
        $this->assertEquals('Firefox', $result[1]['browser']);
        $this->assertEquals(2, $result[1]['click_count']);
    }

    public function test_get_top_os_by_date(): void
    {
        $link = Link::factory()->create();
        $now = now();

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.1',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.2',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.3',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Edge',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.4',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Firefox',
            'os' => 'Windows',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        LinkHistory::factory()->create([
            'link_id' => $link->id,
            'country_name' => 'USA',
            'ip_address' => '192.168.2.5',
            'user_agent' => 'Mozilla/5.0',
            'browser' => 'Firefox',
            'os' => 'macOS',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');

        $endDate = Carbon::now()->addDay()->format('Y-m-d');

        $result = $this->service->getTopOSByDate($startDate, $endDate);

        $this->assertCount(2, $result);
        $this->assertEquals('Windows', $result[0]['os']);
        $this->assertEquals(4, $result[0]['click_count']);
        $this->assertEquals('macOS', $result[1]['os']);
        $this->assertEquals(1, $result[1]['click_count']);
    }

    public function test_error_handling_in_get_total_links(): void
    {
        $mock = $this->mock(Link::class);
        $mock->shouldReceive('query')->andThrow(new \Exception('Database error'));

        $this->assertEquals(0, $this->service->getTotalLinks());
    }

    public function test_empty_result_in_get_daily_clicks(): void
    {
        $result = $this->service->getDailyClicksByDate('2025-01-01', '2025-01-10');

        $expected = array_fill_keys(
            array_map(fn($d) => date('Y-m-d', strtotime("2025-01-{$d}")), range(1, 10)),
            0
        );

        $this->assertEquals($expected, $result);
    }

    public function test_max_days_limit_in_date_range(): void
    {
        Link::factory()->create(['created_at' => '2025-01-01']);
        Link::factory()->create(['created_at' => '2027-01-01']);

        $this->assertEquals(1, $this->service->getTotalLinksByDate('2025-01-01', '2027-01-01'));
    }
}
