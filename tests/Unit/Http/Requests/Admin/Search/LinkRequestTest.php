<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use App\Http\Requests\Admin\Search\LinkRequest;
use Tests\Unit\Http\Requests\RequestTestCase;

class LinkRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return LinkRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'query' => 'example.com/path',
        ];
    }

    public function test_it_validates_correct_data()
    {
        $validQueries = [
            'example.com',
            'https://example.com/path',
            'user@example.com',
            'example.com?query=string',
            'example.com#fragment',
        ];

        foreach ($validQueries as $query) {
            $this->assertValidationPasses(['query' => $query]);
        }
    }

    public function test_it_fails_on_long_query()
    {
        $this->assertValidationFails(
            ['query' => str_repeat('a', 2049)],
            ['query' => 'The query field must not be greater than 2048 characters.']
        );
    }
}
