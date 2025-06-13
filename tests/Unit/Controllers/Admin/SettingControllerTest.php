<?php

namespace Tests\Unit\Controllers\Admin;

use Tests\TestCase;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use App\Http\Requests\Admin\Settings\MaintenanceModeRequest;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UserServiceInterface $userService;
    private SettingController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->controller = new SettingController($this->userService);

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
    }

    public function test_index_returns_view()
    {
        $this->actingAs(User::first());
        $response = $this->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
    }

    /*public function test_successful_maintenance_mode_activation()
    {
        $this->actingAs(User::first());

        $response = $this->post(route('admin.settings.maintenance'), [
            'status' => true
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Maintenance mode activated'
        ]);
        $response->assertJsonStructure(['secret']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }*/
}
