<?php

namespace Tests\Unit\Http\Requests\Admin\Domains;

use App\Http\Requests\Admin\Domains\StoreRequest;
use App\Models\Domain;
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
            'domainName' => 'example.com',
            'domainStatus' => true,
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_domain_name()
    {
        $data = $this->getValidData();
        unset($data['domainName']);

        $this->assertValidationFails(
            $data,
            ['domainName' => 'The domain name field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_domain_format()
    {
        $this->assertValidationFails(
            ['domainName' => 'invalid_domain', 'domainStatus' => true],
            ['domainName' => 'The domain name field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_duplicate_domain()
    {
        Domain::factory()->create(['name' => 'existing.com']);

        $this->assertValidationFails(
            ['domainName' => 'existing.com', 'domainStatus' => true],
            ['domainName' => 'The domain name has already been taken.']
        );
    }

    #[Test]
    public function it_fails_on_missing_domain_status()
    {
        $data = $this->getValidData();
        unset($data['domainStatus']);

        $this->assertValidationFails(
            $data,
            ['domainStatus' => 'The domain status field is required.']
        );
    }

    #[Test]
    public function it_fails_on_non_boolean_status()
    {
        $this->assertValidationFails(
            ['domainName' => 'example.com', 'domainStatus' => 'not-boolean'],
            ['domainStatus' => 'The domain status field must be true or false.']
        );
    }
}
