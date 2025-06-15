<?php

namespace Tests\Unit\Http\Requests\Admin\Links;

use App\Http\Requests\Admin\Links\StoreRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

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
            'custom_name' => 'example-link',
            'user_email' => 'user@example.com',
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
            ['url' => 'invalid-url', 'custom_name' => 'test'],
            ['url' => 'The url field must be a valid URL.']
        );
    }

    #[Test]
    public function it_fails_on_long_url()
    {
        $this->assertValidationFails(
            ['url' => 'https://example.com/'.str_repeat('a', 2048)],
            ['url' => 'The url field must not be greater than 2048 characters.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_custom_name_format()
    {
        $this->assertValidationFails(
            ['url' => 'https://example.com', 'custom_name' => 'invalid@name'],
            ['custom_name' => 'The custom name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_user_email()
    {
        $this->assertValidationFails(
            ['url' => 'https://example.com', 'user_email' => 'invalid-email'],
            ['user_email' => 'The user email field must be a valid email address.']
        );
    }

    #[Test]
    public function it_accepts_null_custom_name_and_user_email()
    {
        $data = $this->getValidData();
        $data['custom_name'] = null;
        $data['user_email'] = null;

        $this->assertValidationPasses($data);
    }
}
