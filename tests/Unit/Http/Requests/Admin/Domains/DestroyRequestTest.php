<?php

namespace Tests\Unit\Http\Requests\Admin\Domains;

use App\Http\Requests\Admin\Domains\DestroyRequest;
use App\Models\Domain;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class DestroyRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return DestroyRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => Domain::factory()->create()->id,
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
}
