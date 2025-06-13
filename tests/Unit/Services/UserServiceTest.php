<?php

namespace Tests\Unit\Services;

use App\Http\Services\UserService;
use App\Models\User;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\AccountRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_get_user_data(): void
    {
        $user = User::factory()->create();
        $result = $this->service->getUserData($user->id);
        
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_get_user_data_not_found(): void
    {
        $result = $this->service->getUserData(999);
        $this->assertNull($result);
    }

    public function test_update_profile(): void
    {
        $user = User::factory()->create();
        $result = $this->service->updateProfile(
            $user->id,
            'New Name',
            'new@example.com'
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create();
        $result = $this->service->updatePassword(
            $user->id,
            'new-password'
        );

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_request_user_data(): void
    {
        $user = User::factory()->create();
        $result = $this->service->requestUserData(
            $user->id,
            'data'
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('account_requests', [
            'user_id' => $user->id,
            'type' => 'data'
        ]);
    }

    public function test_get_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $result = $this->service->getUserByEmail('test@example.com');
        
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_get_top_users(): void
    {
        User::factory()->count(5)->create();
        $result = $this->service->getTopUsers(3);
        
        $this->assertNotNull($result);
        $this->assertCount(3, $result);
    }

    public function test_update_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);

        $result = $this->service->updateUser([
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => 'active',
            'roles' => ['admin']
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_destroy_user(): void
    {
        $user = User::factory()->create();
        $result = $this->service->destroyUser($user->id);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_get_profile(): void
    {
        $user = User::factory()->create();
        $result = $this->service->getProfile($user->id);
        
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_freeze_account(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        
        $result = $this->service->freezeAccount($user->id);

        $this->assertTrue($result);
        $this->assertEquals('freezed', $user->fresh()->status);
        $this->assertFalse(false, $link->fresh()->available);
    }

    public function test_ban_account(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id]);
        LinkHistory::factory()->create(['link_id' => $link->id]);
        
        $result = $this->service->banAccount($user->id);
        
        $this->assertTrue($result);
        $this->assertEquals('banned', $user->fresh()->status);
        $this->assertFalse(false, $link->fresh()->available);
        $this->assertDatabaseMissing('link_histories', ['link_id' => $link->id]);
    }

    public function test_un_account(): void
    {
        $user = User::factory()->create(['status' => 'banned']);
        $result = $this->service->unAccount($user->id);
        
        $this->assertTrue($result);
        $this->assertEquals('active', $user->fresh()->status);
    }

    public function test_search_users(): void
    {
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        Link::factory()->create(['user_id' => $user->id, 'ip_address' => '192.168.1.1']);
        
        // Search by name
        $result = $this->service->searchUsers('Test User', false);
        $this->assertCount(1, $result);
        
        // Search by email
        $result = $this->service->searchUsers('test@example.com', false);
        $this->assertCount(1, $result);
        
        // Search by IP
        $result = $this->service->searchUsers('192.168.1.1', false);
        $this->assertNotEmpty($result);
    }
    
    public function test_empty_result_in_search_users(): void
    {
        $result = $this->service->searchUsers('nonexistent', false);
        $this->assertEmpty($result);
    }
}
