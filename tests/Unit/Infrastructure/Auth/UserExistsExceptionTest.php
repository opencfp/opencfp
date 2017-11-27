<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use OpenCFP\Infrastructure\Auth\UserExistsException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserExistsException
 */
class UserExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testItIsTheCorrectTypeOfException()
    {
        $exception = new UserExistsException();
        $this->assertInstanceOf(\UnexpectedValueException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame(0, $exception->getCode());
    }
}
