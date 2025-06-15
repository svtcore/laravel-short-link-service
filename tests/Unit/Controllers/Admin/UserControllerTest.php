<?php

namespace Tests\Unit\Controllers\Admin;

use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\Admin\UserController;
use App\Http\Services\UserService;
use App\Models\Domain;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UserServiceInterface $userService;

    private LinkServiceInterface $linkService;

    private UserController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService;
        $this->linkService = $this->createMock(LinkServiceInterface::class);
        $this->controller = new UserController($this->userService, $this->linkService);

        try {
            Role::firstOrCreate(['name' => 'admin']);
            Role::firstOrCreate(['name' => 'user']);
        } catch (\Exception $e) {

        }

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
    }

    public function test_index_returns_users_list()
    {
        $this->actingAs(User::first());
        User::factory()->count(5)->create();

        $response = $this->controller->index();

        $this->assertArrayHasKey('users', $response->getData());
        $this->assertNotEmpty($response->getData()['users']);
    }

    public function test_show_user_profile_with_links()
    {
        $this->actingAs(User::first());
        $user = User::factory()->create();
        Link::factory()->count(3)->create(['user_id' => $user->id, 'domain_id' => Domain::factory()->create()->id]);

        $response = $this->get(route('admin.users.show', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertViewHas('user');
        $response->assertViewHas('links');
    }

    public function test_update_user_info()
    {
        $this->actingAs(User::first());
        $user = User::factory()->create();

        $response = $this->put(route('admin.users.update', $user->id), [
            'id' => $user->id,
            'editName' => 'Updated Name',
            'editEmail' => 'example123@user.com',
            'editStatus' => 'active',
            'editRoles' => ['user'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'User successfully updated');
    }

    public function test_delete_user()
    {
        $this->actingAs(User::first());
        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user->id));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $response->assertStatus(302);
        $response->assertSessionHas('success', 'User data and related links, histories successfully deleted');
    }

    public function test_ban_user()
    {
        $this->actingAs(User::first());
        $user = User::factory()->create();

        $response = $this->put(route('admin.users.ban', ['id' => $user->id]));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'User has been banned, related links were disabled');
    }

    public function test_handle_errors_when_fetching_users()
    {
        $mockUserService = $this->createMock(UserServiceInterface::class);
        $mockUserService->method('getTopUsers')->willThrowException(new \Exception);

        $mockLinkService = $this->createMock(LinkServiceInterface::class);
        $controller = new UserController($mockUserService, $mockLinkService);

        $response = $controller->index();
        $this->assertEmpty($response->getData()['users']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
