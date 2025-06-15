<?php

namespace Tests\Unit\Http\Requests\Admin\Links;

use App\Http\Requests\Admin\Links\UpdateRequest;
use App\Models\Link;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return UpdateRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => Link::factory()->create()->id,
            'url' => 'https://updated.com',
            'custom_name' => 'updated-link',
            'status' => true,
        ];
    }

    #[Test]
    public function it_prepares_data_correctly()
    {
        $request = new UpdateRequest;
        $request->merge([
            'editURL' => 'https://example.com',
            'editCustomName' => 'test',
            'editStatus' => true,
        ]);

        $route = new Route('PUT', '/links/{id}', []);
        $route->bind($request);
        $route->setParameter('id', 123);
        $request->setRouteResolver(fn () => $route);

        $request->prepareForValidation();

        $this->assertEquals([
            'editURL' => 'https://example.com',
            'editCustomName' => 'test',
            'editStatus' => true,
            'id' => 123,
            'url' => 'https://example.com',
            'custom_name' => 'test',
            'status' => true,
        ], $request->all());
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_url()
    {
        $data = $this->getValidData();
        unset($data['url']);

        $this->assertValidationFails(
            $data,
            ['url' => 'The url field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_url_format()
    {
        $this->assertValidationFails(
            ['id' => 1, 'url' => 'invalid-url', 'custom_name' => 'test'],
            ['url' => 'The url field must be a valid URL.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_custom_name_format()
    {
        $this->assertValidationFails(
            ['id' => 1, 'url' => 'https://example.com', 'custom_name' => 'invalid@name'],
            ['custom_name' => 'The custom name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_link_id()
    {
        $this->assertValidationFails(
            ['id' => 999999, 'url' => 'https://example.com'],
            ['id' => 'The selected id is invalid.']
        );
    }

    #[Test]
    public function it_accepts_null_custom_name_and_status()
    {
        $data = $this->getValidData();
        $data['custom_name'] = null;
        $data['status'] = null;

        $this->assertValidationPasses($data);
    }
}
