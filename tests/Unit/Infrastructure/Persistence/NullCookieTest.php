<?php

namespace OpenCFP\Test\Unit\Infrastructure\Persistence;

use OpenCFP\Infrastructure\Persistence\NullCookie;

/**
 * @covers \OpenCFP\Infrastructure\Persistence\NullCookie
 */
class NullCookieTest extends \PHPUnit\Framework\TestCase
{
    public function testPutDoesNothing()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->put('hi'));
    }

    public function testGetDoesNothing()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->get());
    }

    public function testForgetDoesNothing()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->forget());
    }
}
