<?php

namespace Tests\Unit\Controllers\User;

use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\User\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private UserController $controller;

    private User $user;

    private $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->controller = new UserController($this->userService);

        Role::firstOrCreate(['name' => 'user']);
        $this->user = User::factory()->create(['password' => bcrypt('password')]);
        $this->user->assignRole('user');
    }

    public function test_update_profile_redirects_with_success()
    {
        $this->userService->method('updateProfile')
            ->willReturn(true);

        $response = $this->actingAs($this->user)
            ->post(route('user.settings.data'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $response->assertRedirect(route('user.settings.index'));
        $response->assertSessionHas('success');
    }

    public function test_update_password_redirects_with_success()
    {
        $this->userService->method('updatePassword')
            ->willReturn(true);

        $response = $this->actingAs($this->user)
            ->put(route('user.settings.password'), [
                'password' => 'password',
                'new_password' => 'NewP@ssw0rd123!',
                'new_password_confirmation' => 'NewP@ssw0rd123!',
            ]);

        $response->assertRedirect(route('user.settings.index'));
        $response->assertSessionHas('success');
    }
}
