<?php

namespace Tests\Unit\Http\Requests\Admin\Domains;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Domains\UpdateRequest;
use App\Models\Domain;

class UpdateRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return UpdateRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => Domain::factory()->create()->id,
            'domainName' => 'updated.com',
            'domainStatus' => false
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
    public function it_fails_on_missing_domain_id()
    {
        $data = $this->getValidData();
        unset($data['id']);

        $this->assertValidationFails(
            $data,
            ['id' => 'The id field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_domain_format()
    {
        $this->assertValidationFails(
            ['id' => 1, 'domainName' => 'invalid_domain', 'domainStatus' => true],
            ['domainName' => 'The domain name field format is invalid.']
        );
    }
}
