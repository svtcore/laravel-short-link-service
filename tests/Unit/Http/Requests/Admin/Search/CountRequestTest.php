<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Search\CountRequest;

class CountRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return CountRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'query' => 'example search'
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
        $this->assertValidationPasses($this->getValidData());
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

    #[Test]
    public function it_accepts_valid_search_characters()
    {
        $validQueries = [
            'regular search',
            'search-with-hyphens',
            'search.with.dots',
            'search:with:colons',
            'search/with/slashes',
            'search[with]brackets'
        ];

        foreach ($validQueries as $query) {
            $this->assertValidationPasses(['query' => $query]);
        }
    }
}
