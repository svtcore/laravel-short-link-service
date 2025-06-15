<?php

namespace Tests\Unit\Controllers\Admin;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles_list = ['admin', 'user'];
        foreach ($roles_list as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Creating specific users with roles
        $admin = $this->createUser('John Doe', 'admin@admin.com', 'admin', Hash::make('password'));
        $admin->assignRole('user');
        $user = $this->createUser('Test user', 'user@user.com', 'user', Hash::make('password'));
        $link = Link::factory()->create(['user_id' => $user->id]);
        LinkHistory::factory()->create(['link_id' => $link->id]);
    }

    private function createUser(?string $name = null, ?string $email = null, string $role = 'user', ?string $password = null): User
    {
        if ($password == null) {
            $password = Hash::make(Str::random(16));
        }
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

    public function test_index_returns_view(): void
    {
        $response = $this->actingAs(User::first())
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('total_links');
        $response->assertViewHas('total_clicks');
    }

    public function test_show_returns_expected_dashboard_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Bus::fake();

        $startDate = now()->subDay()->startOfDay()->toDateString();
        $endDate = now()->endOfDay()->toDateString();

        $mockData = [
            'total_links_by_date' => 10,
            'total_clicks_by_date' => 100,
            'total_unique_clicks_by_date' => 80,
            'total_users_by_date' => 5,
            'total_daily_clicks' => [1, 2, 3],
            'total_time_clicks' => [0 => 5],
            'chart_top_countries_by_date' => ['US' => 50],
            'chart_top_browsers_by_date' => ['Chrome' => 70],
            'chart_top_platforms_by_date' => ['Windows' => 40],
        ];

        foreach ($mockData as $key => $value) {
            $cacheKey = "{$key}_{$startDate}_{$endDate}";
            Cache::shouldReceive('get')
                ->with($cacheKey)
                ->andReturn($value);
        }

        $response = $this->actingAs($user)
            ->getJson(route('admin.dashboard.show', [
                'startDate' => now()->subDay()->format('Y-m-d'),
                'endDate' => now()->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertJson(
                fn (AssertableJson $json) => $json->hasAll([
                    'total_links_by_date',
                    'total_clicks_by_date',
                    'total_unique_clicks_by_date',
                    'total_users_by_date',
                    'chart_days_activity_data',
                    'chart_time_activity_data',
                    'chart_geo_data',
                    'chart_browser_data',
                    'chart_platform_data',
                ])
            );
    }

    public function test_show_with_empty_dates(): void
    {
        $response = $this->actingAs(User::first())
            ->get(route('admin.dashboard.show'));

        $response->assertStatus(200);
    }
}
