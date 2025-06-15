<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use App\Http\Requests\Admin\Search\DomainRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class DomainRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return DomainRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'query' => 'example.com',
        ];
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
    public function it_fails_on_invalid_domain_characters()
    {
        $this->assertValidationFails(
            ['query' => 'example@domain.com'],
            ['query' => 'The query field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_long_domain()
    {
        $this->assertValidationFails(
            ['query' => str_repeat('a', 254)],
            ['query' => 'The query field must not be greater than 253 characters.']
        );
    }

    #[Test]
    public function it_accepts_valid_domain_formats()
    {
        $validDomains = [
            'example.com',
            'sub.example.com',
            'example-domain.com',
            '123domain.com',
            'xn--example-9ua.com', // IDN domain
        ];

        foreach ($validDomains as $domain) {
            $this->assertValidationPasses(['query' => $domain]);
        }
    }
}
