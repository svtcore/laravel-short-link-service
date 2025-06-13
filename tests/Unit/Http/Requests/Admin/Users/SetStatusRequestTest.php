<?php

namespace Tests\Unit\Http\Requests\Admin\Users;

use PHPUnit\Framework\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Users\SetStatusRequest;
use App\Models\User;

class SetStatusRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return SetStatusRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => User::factory()->create()->id
        ];
    }

    #[Test]
    public function it_authorizes_admin_users()
    {
        $this->testAuthorization();
    }

    #[Test]
    public function it_prepares_data_correctly()
    {
        $request = new SetStatusRequest();
        $request->setRouteResolver(fn () => new \Illuminate\Routing\Route('PUT', '/admin/users/{id}/status', ['id' => 123]));
        
        $request->prepareForValidation();
        
        $this->assertEquals(['id' => 123], $request->all());
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
    public function it_fails_on_invalid_user_id()
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
