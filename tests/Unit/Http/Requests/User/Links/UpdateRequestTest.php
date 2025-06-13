<?php

namespace Tests\Unit\Http\Requests\User\Links;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\User\Links\UpdateRequest;
use App\Models\Link;

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
            'custom_name' => 'Updated Link',
            'destination' => 'https://updated.example.com',
            'access' => true
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_id()
    {
        $this->assertValidationFails(
            [
                'custom_name' => 'Updated Link',
                'destination' => 'https://updated.example.com'
            ],
            ['id' => 'The id field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_link_id()
    {
        $this->assertValidationFails(
            ['id' => 999999],
            ['id' => 'The selected id is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_custom_name_format()
    {
        $this->assertValidationFails(
            ['custom_name' => 'Invalid@Name'],
            ['custom_name' => 'The custom name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_long_custom_name()
    {
        $this->assertValidationFails(
            ['custom_name' => str_repeat('a', 256)],
            ['custom_name' => 'The custom name field must not be greater than 255 characters.']
        );
    }

    #[Test]
    public function it_fails_on_missing_destination()
    {
        $this->assertValidationFails(
            ['custom_name' => 'Updated Link'],
            ['destination' => 'The destination field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_destination_url()
    {
        $this->assertValidationFails(
            ['destination' => 'not-a-url'],
            ['destination' => 'The destination field must be a valid URL.']
        );
    }

    #[Test]
    public function it_fails_on_long_destination()
    {
        $this->assertValidationFails(
            ['destination' => 'https://example.com/' . str_repeat('a', 2048)],
            ['destination' => 'The destination field must not be greater than 2048 characters.']
        );
    }

    #[Test]
    public function it_fails_on_missing_access()
    {
        $this->assertValidationFails(
            [
                'id' => 1,
                'destination' => 'https://example.com'
            ],
            ['access' => 'The access field is required.']
        );
    }

    #[Test]
    public function it_fails_on_non_boolean_access()
    {
        $this->assertValidationFails(
            ['access' => 'not-a-boolean'],
            ['access' => 'The access field must be true or false.']
        );
    }
}
