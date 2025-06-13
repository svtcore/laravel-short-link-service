<?php

namespace Tests\Unit\Controllers\Admin;

use Tests\TestCase;
use App\Models\Domain;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Http\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Requests\Admin\Domains\StoreRequest;
use App\Http\Requests\Admin\Domains\UpdateRequest;
use App\Http\Requests\Admin\Domains\DestroyRequest;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DomainControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private DomainService $service;
    private DomainController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DomainService();
        $this->controller = new DomainController($this->service);

        $roles_list = ['admin', 'user'];
        foreach ($roles_list as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Creating specific users with roles
        $admin = $this->createUser('John Doe', 'admin@admin.com', 'admin', Hash::make("password"));
        $admin->assignRole('user');
        $user = $this->createUser('Test user', 'user@user.com', 'user', Hash::make("password"));
    }

    private function createUser(string $name = null, string $email = null, string $role = 'user', string $password = null): User
    {
        if ($password == null)
            $password = Hash::make(Str::random(16));
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

    public function test_get_list_of_domains()
    {
        $this->actingAs(User::first());

        $domain = Domain::factory()->create();
        $link = Link::factory()->create(['domain_id' => $domain->id]);
        LinkHistory::factory()->count(3)->create(['link_id' => $link->id]);

        $response = $this->controller->index();

        $this->assertArrayHasKey('domains', $response->getData());
        $this->assertNotEmpty($response->getData()['domains']);
    }

    public function test_store_new_domain()
    {
        $this->actingAs(User::first());

        $response = $this->post(route('admin.domains.store'), [
            'domainName' => 'example.com',
            'domainStatus' => true,
        ]);

        $this->assertDatabaseHas('domains', [
            'name' => 'example.com',
            'available' => true,
        ]);

        $response->assertRedirect();

        $response->assertSessionHas('success', 'Domain successfully added.');
    }

    public function test_update_domain()
    {
        $this->actingAs(User::first());

        $domain = Domain::factory()->create(['name' => 'old.com', 'available' => false]);

        $response = $this->put(route('admin.domains.update', $domain->id), [
            'id' => $domain->id,
            'domainName' => 'new.com',
            'domainStatus' => true,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'name' => 'new.com',
            'available' => true,
        ]);
    }


    public function test_delete_domain()
    {
        $this->actingAs(User::first());

        $domain = Domain::factory()->create();

        $response = $this->delete(route('admin.domains.destroy', $domain->id),
            ['id' => $domain->id]
        );

        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_handle_errors_when_fetching_domains()
    {
        $this->actingAs(User::first());
        $mockService = $this->createMock(DomainService::class);
        $mockService->method('getDomainsList')->willThrowException(new \Exception());

        $controller = new DomainController($mockService);
        $response = $controller->index();

        $this->assertArrayHasKey('error', $response->getData());
    }
}
