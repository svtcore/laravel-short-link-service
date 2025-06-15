<?php

namespace Tests\Unit\Http\Requests\User\Profile;

use App\Http\Requests\User\Profile\UpdatePasswordRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdatePasswordRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return UpdatePasswordRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'password' => 'current_password_123!',
            'new_password' => 'new_password_123!',
            'new_password_confirmation' => 'new_password_123!',
        ];
    }

    #[Test]
    public function it_fails_on_missing_current_password()
    {
        $this->assertValidationFails(
            ['new_password' => 'new_password_123!'],
            ['password' => 'The password field is required.']
        );
    }

    #[Test]
    public function it_fails_on_incorrect_current_password()
    {
        $this->assertValidationFails(
            ['password' => 'wrong_password'],
            ['password' => 'The password is incorrect.']
        );
    }

    #[Test]
    public function it_fails_on_missing_new_password()
    {
        $this->assertValidationFails(
            ['password' => 'current_password_123!'],
            ['new_password' => 'The new password field is required.']
        );
    }

    #[Test]
    public function it_fails_on_short_new_password()
    {
        $this->assertValidationFails(
            ['new_password' => 'short'],
            ['new_password' => 'The new password field must be at least 8 characters.']
        );
    }

    #[Test]
    public function it_fails_on_unconfirmed_new_password()
    {
        $this->assertValidationFails(
            [
                'new_password' => 'new_password_123!',
                'new_password_confirmation' => 'different',
            ],
            ['new_password' => 'The new password field confirmation does not match.']
        );
    }

    #[Test]
    public function it_fails_on_weak_new_password()
    {
        $this->assertValidationFails(
            ['new_password' => 'simplepassword'],
            ['new_password' => 'The new password field format is invalid.']
        );
    }
}
