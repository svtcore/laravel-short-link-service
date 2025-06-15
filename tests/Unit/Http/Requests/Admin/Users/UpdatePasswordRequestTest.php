<?php

namespace Tests\Unit\Http\Requests\Admin\Users;

use App\Http\Requests\Admin\Users\UpdatePasswordRequest;
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
            'password' => 'password', // Default Laravel test password
            'new_password' => 'NewP@ssw0rd',
            'new_password_confirmation' => 'NewP@ssw0rd',
        ];
    }

    #[Test]
    public function it_fails_on_missing_current_password()
    {
        $this->assertValidationFails(
            ['new_password' => 'NewP@ssw0rd'],
            ['password' => 'The password field is required.']
        );
    }

    #[Test]
    public function it_fails_on_missing_new_password()
    {
        $this->assertValidationFails(
            ['password' => 'current_password'],
            ['new_password' => 'The new password field is required.']
        );
    }

    #[Test]
    public function it_fails_on_weak_password()
    {
        $weakPasswords = [
            'short',
            'n0symbols',
            'nouppercase',
            'NOLOWERCASE',
        ];

        foreach ($weakPasswords as $password) {
            $this->assertValidationFails(
                [
                    'password' => 'current_password',
                    'new_password' => $password,
                    'new_password_confirmation' => $password,
                ],
                ['new_password' => 'The new password field format is invalid.']
            );
        }
    }

    #[Test]
    public function it_fails_on_unconfirmed_password()
    {
        $this->assertValidationFails(
            [
                'password' => 'current_password',
                'new_password' => 'NewP@ssw0rd',
                'new_password_confirmation' => 'Different1!',
            ],
            ['new_password' => 'The new password field confirmation does not match.']
        );
    }

    #[Test]
    public function it_fails_on_short_password()
    {
        $this->assertValidationFails(
            [
                'password' => 'current_password',
                'new_password' => 'Short1!',
                'new_password_confirmation' => 'Short1!',
            ],
            ['new_password' => 'The new password field must be at least 8 characters.']
        );
    }
}
