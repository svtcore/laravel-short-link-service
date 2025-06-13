<?php

namespace Tests\Unit\Controllers\Admin;

use Tests\TestCase;
use App\Models\Link;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Services\UserService;
use App\Http\Services\LinkService;
use App\Http\Services\DomainService;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Admin\Search\CountRequest;
use App\Http\Requests\Admin\Search\DomainRequest;
use App\Http\Requests\Admin\Search\UserRequest;
use App\Http\Requests\Admin\Search\LinkByDomainRequest;
use App\Http\Requests\Admin\Search\LinkByIPRequest;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UserService $userService;
    private LinkService $linkService;
    private DomainService $domainService;
    private SearchController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService();
        $this->linkService = new LinkService();
        $this->domainService = new DomainService();

        $this->controller = new SearchController(
            $this->userService,
            $this->linkService,
            $this->domainService
        );

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        $admin->assignRole('admin');

        Domain::factory()->count(3)->create();
        User::factory()->count(2)->create();
        Link::factory()->count(5)->create();
    }

    public function test_search_count_returns_correct_counts()
    {
        $this->actingAs(User::first());

        $response = $this->get(route('admin.search.count', ['query' => 'test']));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(0, $data['links']);
        $this->assertEquals(0, $data['domains']);
        $this->assertEquals(0, $data['users']);
    }

    public function test_search_domains_returns_results()
    {
        $this->actingAs(User::first());

        Domain::factory()->create(['name' => 'example.com']);

        $response = $this->get(route('admin.search.domains', ['query' => 'example']));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->viewData('results'));
    }

    public function test_search_users_returns_results()
    {
        $this->actingAs(User::first());

        User::factory()->create(['name' => 'user_1']);
        User::factory()->create(['name' => 'another_user']);

        $response = $this->get(route('admin.search.users', ['query' => 'user']));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->viewData('results'));
    }

    public function test_search_links_returns_results()
    {
        $this->actingAs(User::first());

        $link = Link::factory()->create([
            'domain_id' => Domain::first()->id, 
            'destination' => 'https://example.com',
            'short_name' => 'test123',
            'ip_address' => '192.168.1.1'
        ]);

        $response = $this->get(route('admin.search.links', ['query' => 'example']));

        $response->assertStatus(200);
        $this->assertTrue($response->original instanceof \Illuminate\View\View);
        $this->assertNotEmpty($response->viewData('results'));
    }

    public function test_search_links_by_domain_returns_results()
    {
        $this->actingAs(User::first());

        $domain = Domain::factory()->create(['name' => 'example.com']);
        Link::factory()->create(['domain_id' => $domain->id]);

        $response = $this->get(route('admin.search.links.byDomainId', ['id' => $domain->id]));

        $response->assertStatus(200);
        $this->assertTrue($response->original instanceof \Illuminate\View\View);
        $this->assertNotEmpty($response->viewData('results'));
    }

    public function test_search_links_by_ip_returns_results()
    {
        $this->actingAs(User::first());

        $link = Link::factory()->create(['domain_id' => Domain::first()->id,'ip_address' => '192.168.1.1']);

        $response = $this->get(route('admin.search.links.byUserIP', ['ip' => $link->ip_address]));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->viewData('results'));
    }

    public function test_search_count_handles_errors()
    {
        $this->actingAs(User::first());

        $mockService = $this->createMock(LinkService::class);
        $mockService->method('searchLinks')->willThrowException(new \Exception());

        $this->app->instance(LinkService::class, $mockService);

        $response = $this->get(route('admin.search.count', ['query' => 'test']));

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->json());
    }
}
