<?php

namespace Tests\Unit\Http\Requests\User\Links;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\User\Links\StoreRequest;

class StoreRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return StoreRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'url' => 'https://example.com',
            'custom_name' => 'My Link',
            'from_modal' => true
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_url()
    {
        $this->assertValidationFails(
            ['custom_name' => 'My Link'],
            ['url' => 'The url field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_url()
    {
        $this->assertValidationFails(
            ['url' => 'not-a-url'],
            ['url' => 'The url field must be a valid URL.']
        );
    }

    #[Test]
    public function it_fails_on_long_url()
    {
        $this->assertValidationFails(
            ['url' => 'https://example.com/' . str_repeat('a', 2048)],
            ['url' => 'The url field must not be greater than 2048 characters.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_custom_name_format()
    {
        $this->assertValidationFails(
            [
                'url' => 'https://example.com',
                'custom_name' => 'Invalid@Name'
            ],
            ['custom_name' => 'The custom name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_long_custom_name()
    {
        $this->assertValidationFails(
            [
                'url' => 'https://example.com',
                'custom_name' => str_repeat('a', 256)
            ],
            ['custom_name' => 'The custom name field must not be greater than 255 characters.']
        );
    }

    #[Test]
    public function it_fails_on_non_boolean_from_modal()
    {
        $this->assertValidationFails(
            [
                'url' => 'https://example.com',
                'from_modal' => 'not-a-boolean'
            ],
            ['from_modal' => 'The from modal field must be true or false.']
        );
    }
}
