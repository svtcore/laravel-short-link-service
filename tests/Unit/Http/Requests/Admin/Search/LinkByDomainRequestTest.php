<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use App\Http\Requests\Admin\Search\LinkByDomainRequest;
use App\Models\Domain;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class LinkByDomainRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return LinkByDomainRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => Domain::factory()->create()->id,
        ];
    }

    #[Test]
    public function it_prepares_data_correctly()
    {
        $request = new LinkByDomainRequest;

        $route = new Route('GET', '/search/links/domain/{id}', []);
        $route->bind($request);
        $route->setParameter('id', 123);
        $request->setRouteResolver(fn () => $route);

        $request->prepareForValidation();

        $this->assertEquals(['id' => 123], $request->all());
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_domain_id()
    {
        $this->assertValidationFails(
            [],
            ['id' => 'The id field is required.']
        );
    }

    #[Test]
    public function it_fails_on_non_integer_id()
    {
        $this->assertValidationFails(
            ['id' => 'string'],
            ['id' => 'The id field must be an integer.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_domain_id()
    {
        $this->assertValidationFails(
            ['id' => 999999],
            ['id' => 'The selected id is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_id_less_than_one()
    {
        $this->assertValidationFails(
            ['id' => 0],
            ['id' => 'The id field must be at least 1.']
        );
    }
}
