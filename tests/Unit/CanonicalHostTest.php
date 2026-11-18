<?php

namespace Tests\Unit;

use App\Http\Middleware\CanonicalHost;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHostTest extends TestCase
{
    public function test_other_hosts_and_post_requests_are_passed_through(): void
    {
        foreach ([['https://darlii.com/properties', 'GET'], ['http://localhost/properties', 'GET'], ['https://www.darlii.com/contact-us', 'POST']] as [$url, $method]) {
            $expected = new Response('passed');
            $actual = (new CanonicalHost)->handle(Request::create($url, $method), fn () => $expected);
            $this->assertSame($expected, $actual);
        }
    }
}
