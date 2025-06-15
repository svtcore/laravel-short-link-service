<?php

namespace Tests\Unit\Http\Requests\Admin\Users;

use App\Http\Requests\Admin\Users\UpdateRequest;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::query()->delete();
    }

    protected function getRequestClass(): string
    {
        return UpdateRequest::class;
    }

    protected function getValidData(): array
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        return [
            'id' => User::factory()->create()->id,
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'status' => 'active',
            'roles' => ['admin'],
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_id()
    {
        $this->assertValidationFails(
            [
                'name' => 'Valid Name',
                'email' => 'valid@example.com',
                'status' => 'active',
                'roles' => ['admin'],
            ],
            ['id' => 'The id field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_name_format()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'name' => 'Invalid@Name',
                'email' => 'valid@example.com',
                'status' => 'active',
                'roles' => ['admin'],
            ],
            ['name' => 'The name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_missing_email()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'name' => 'Valid Name',
                'status' => 'active',
                'roles' => ['admin'],
            ],
            ['email' => 'The email field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_status()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'name' => 'Valid Name',
                'email' => 'valid@example.com',
                'status' => 'invalid',
                'roles' => ['admin'],
            ],
            ['status' => 'The selected status is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_missing_roles()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'name' => 'Valid Name',
                'email' => 'valid@example.com',
                'status' => 'active',
            ],
            ['roles' => 'The roles field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_role()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'name' => 'Valid Name',
                'email' => 'valid@example.com',
                'status' => 'active',
                'roles' => ['invalid-role'],
            ],
            ['roles.0' => 'The selected roles.0 is invalid.']
        );
    }
}
