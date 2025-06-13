<?php

namespace Tests\Unit\Http\Requests\Admin\Settings;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Settings\MaintenanceModeRequest;

class MaintenanceModeRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return MaintenanceModeRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'status' => true
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
        $this->assertValidationPasses(['status' => true]);
        $this->assertValidationPasses(['status' => false]);
    }

    #[Test]
    public function it_fails_on_missing_status()
    {
        $this->assertValidationFails(
            [],
            ['status' => 'The status field is required.']
        );
    }

    #[Test]
    public function it_fails_on_non_boolean_status()
    {
        $this->assertValidationFails(
            ['status' => 'not-a-boolean'],
            ['status' => 'The status field must be true or false.']
        );
    }
}
