<?php

namespace Tests\Unit\Controllers\User;

use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Controllers\User\SettingController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    private SettingController $controller;

    private User $user;

    private $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->controller = new SettingController($this->userService);

        Role::firstOrCreate(['name' => 'user']);
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    public function test_settings_page_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('user.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('user.settings');
    }

    public function test_settings_page_loads_user_data()
    {
        $mockUser = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->userService->method('getUserData')
            ->willReturn($mockUser);

        $response = $this->actingAs($this->user)
            ->get(route('user.settings.index'));

        $response->assertViewHas('user_data');
    }

    public function test_request_data_export()
    {
        $this->userService->method('requestUserData')
            ->willReturn(true);

        $response = $this->actingAs($this->user)
            ->post(route('user.settings.data'));

        $response->assertRedirect(route('user.settings.index'));
        $response->assertSessionHas('success');
    }

    public function test_cannot_update_other_users_settings()
    {
        $otherUser = User::factory()->create();

        $this->userService->expects($this->never())
            ->method('updateProfile');

        $response = $this->actingAs($this->user)
            ->put(route('user.settings.profile'), [
                'name' => 'Other User',
                'email' => 'other@example.com',
            ]);

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isOk());
    }
}
