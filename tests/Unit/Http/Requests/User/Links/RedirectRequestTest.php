<?php

namespace Tests\Unit\Http\Requests\User\Links;

use App\Http\Requests\User\Links\RedirectRequest;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class RedirectRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return RedirectRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'host' => 'example.com',
            'path' => 'abc123',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'ip' => '192.168.1.1',
        ];
    }

    #[Test]
    public function it_authorizes_all_requests()
    {
        $request = new RedirectRequest($this->getValidData());
        $request->setContainer(new Container);
        $this->assertTrue($request->authorize());
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_host()
    {
        $this->assertValidationFails(
            array_diff_key($this->getValidData(), ['host' => '']),
            ['host' => 'The host field is required.']
        );
    }

    #[Test]
    public function it_fails_on_long_host()
    {
        $this->assertValidationFails(
            ['host' => str_repeat('a', 256)],
            ['host' => 'The host field must not be greater than 255 characters.']
        );
    }

    #[Test]
    public function it_fails_on_short_path()
    {
        $this->assertValidationFails(
            ['path' => 'ab'],
            ['path' => 'The path field must be at least 3 characters.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_path_format()
    {
        $this->assertValidationFails(
            ['path' => 'invalid@path'],
            ['path' => 'The path field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_missing_user_agent()
    {
        $this->assertValidationFails(
            array_diff_key($this->getValidData(), ['user_agent' => '']),
            ['user_agent' => 'The user agent field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_user_agent_format()
    {
        $this->assertValidationFails(
            ['user_agent' => 'Invalid@Agent'],
            ['user_agent' => 'The user agent field format is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_missing_ip()
    {
        $this->assertValidationFails(
            array_diff_key($this->getValidData(), ['ip' => '']),
            ['ip' => 'The ip field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_ip()
    {
        $this->assertValidationFails(
            ['ip' => 'not.an.ip'],
            ['ip' => 'The ip field must be a valid IP address.']
        );
    }
}
