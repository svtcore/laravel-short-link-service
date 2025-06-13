<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Search\UserRequest;

class UserRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return UserRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'query' => 'username123'
        ];
    }

    #[Test]
    public function it_authorizes_admin_users()
    {
        $this->testAuthorization();
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $validQueries = [
            'username',
            'user.name',
            'user-name',
            'user@example.com',
            'user123'
        ];

        foreach ($validQueries as $query) {
            $this->assertValidationPasses(['query' => $query]);
        }
    }

    #[Test]
    public function it_fails_on_missing_query()
    {
        $this->assertValidationFails(
            [],
            ['query' => 'The query field is required.']
        );
    }

    #[Test]
    public function it_fails_on_long_query()
    {
        $this->assertValidationFails(
            ['query' => str_repeat('a', 2049)],
            ['query' => 'The query field must not be greater than 2048 characters.']
        );
    }
}
