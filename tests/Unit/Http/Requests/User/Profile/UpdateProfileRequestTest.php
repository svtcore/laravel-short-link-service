<?php

namespace Tests\Unit\Http\Requests\User\Profile;

use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateProfileRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return UpdateProfileRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_accepts_null_name()
    {
        $data = $this->getValidData();
        $data['name'] = null;
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function it_fails_on_invalid_name_format()
    {
        $this->assertValidationFails(
            ['name' => 'Invalid@Name'],
            ['name' => 'The name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_long_name()
    {
        $this->assertValidationFails(
            ['name' => str_repeat('a', 256)],
            ['name' => 'The name field must not be greater than 255 characters.']
        );
    }

    #[Test]
    public function it_fails_on_missing_email()
    {
        $this->assertValidationFails(
            ['name' => 'Updated Name'],
            ['email' => 'The email field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_email()
    {
        $this->assertValidationFails(
            ['email' => 'not-an-email'],
            ['email' => 'The email field must be a valid email address.']
        );
    }

    #[Test]
    public function it_fails_on_duplicate_email()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $this->assertValidationFails(
            ['email' => 'existing@example.com'],
            ['email' => 'The email has already been taken.']
        );
    }
}
