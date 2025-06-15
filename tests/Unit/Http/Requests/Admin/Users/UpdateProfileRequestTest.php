<?php

namespace Tests\Unit\Http\Requests\Admin\Users;

use App\Http\Requests\Admin\Users\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
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
            'name' => 'Valid Name 123',
            'email' => 'valid@example.com',
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_name()
    {
        $this->assertValidationFails(
            ['email' => 'valid@example.com'],
            ['name' => 'The name field is required.']
        );
    }

    #[Test]
    public function it_fails_on_missing_email()
    {
        $this->assertValidationFails(
            ['name' => 'Valid Name'],
            ['email' => 'The email field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_name_format()
    {
        $invalidNames = [
            'Name@Invalid',
            'Name#Invalid',
            'Name$Invalid',
        ];

        foreach ($invalidNames as $name) {
            $this->assertValidationFails(
                [
                    'name' => $name,
                    'email' => 'valid@example.com',
                ],
                ['name' => 'The name field format is invalid.']
            );
        }
    }

    #[Test]
    public function it_fails_on_invalid_email()
    {
        $this->assertValidationFails(
            [
                'name' => 'Valid Name',
                'email' => 'invalid-email',
            ],
            ['email' => 'The email field must be a valid email address.']
        );
    }

    #[Test]
    public function it_fails_on_duplicate_email()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $this->assertValidationFails(
            [
                'name' => 'Valid Name',
                'email' => 'existing@example.com',
            ],
            ['email' => 'The email has already been taken.']
        );
    }

    #[Test]
    public function it_allows_own_email()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $requestClass = $this->getRequestClass();
        $request = new $requestClass;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make(
            ['name' => 'Valid Name', 'email' => 'existing@example.com'],
            $request->rules(),
            [],
            ['email' => $user->email] // Pass current email as input data
        );

        $this->assertFalse($validator->fails());
    }
}
