<?php

namespace Tests\Unit\Http\Requests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Models\Role;

abstract class RequestTestCase extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        $this->adminUser = User::factory()->create([
            'password' => bcrypt('password')
        ])->assignRole('admin');
        $this->regularUser = User::factory()->create()->assignRole('user');
    }

    abstract protected function getRequestClass(): string;
    abstract protected function getValidData(): array;

    protected function validate(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $requestClass = $this->getRequestClass();
        $request = new $requestClass();
        $request->setUserResolver(fn () => $this->adminUser);
        
        return Validator::make($data, $request->rules());
    }

    protected function assertValidationPasses(array $data)
    {
        $validator = $this->validate($data);
        $this->assertFalse($validator->fails(), 
            "Expected validation to pass but failed with errors: " . json_encode($validator->errors()->toArray()));
    }

    protected function assertValidationFails(array $data, array $expectedErrors)
    {
        $validator = $this->validate($data);
        $this->assertTrue($validator->fails());
        
        $errors = $validator->errors()->toArray();
        foreach ($expectedErrors as $field => $message) {
            $this->assertArrayHasKey($field, $errors, "Expected error for field '$field' but not found");
            $this->assertContains($message, $errors[$field], 
                "Expected error message '$message' not found in: " . json_encode($errors[$field]));
        }
    }

    protected function testAuthorization()
    {
        $requestClass = $this->getRequestClass();
        $request = new $requestClass();
        
        // Test admin authorization
        $request->setUserResolver(fn () => $this->adminUser);
        $this->assertTrue($request->authorize(), 'Admin should be authorized');

        // Test regular user authorization
        $request->setUserResolver(fn () => $this->regularUser);
        $this->assertFalse($request->authorize(), 'Regular user should not be authorized');
    }
}
