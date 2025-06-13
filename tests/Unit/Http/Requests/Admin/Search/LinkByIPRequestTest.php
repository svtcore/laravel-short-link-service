<?php

namespace Tests\Unit\Http\Requests\Admin\Search;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Admin\Search\LinkByIPRequest;

class LinkByIPRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return LinkByIPRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'ip' => '192.168.1.1'
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
        $validIPs = [
            '192.168.1.1', // IPv4
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334', // Full IPv6
            '2001:db8::8a2e:370:7334' // Mixed IPv6
        ];

        foreach ($validIPs as $ip) {
            $this->assertValidationPasses(['ip' => $ip]);
        }
    }

    #[Test]
    public function it_fails_on_missing_ip()
    {
        $this->assertValidationFails(
            [],
            ['ip' => 'The ip field is required.']
        );
    }
}
