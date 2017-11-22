<?php

namespace OpenCFP\Test\Unit\Domain\Services;

use OpenCFP\Domain\Services\AuthenticationException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Services\AuthenticationException
 */
final class AuthenticationExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new AuthenticationException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
