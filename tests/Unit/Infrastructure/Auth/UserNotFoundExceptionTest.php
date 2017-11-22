<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use OpenCFP\Infrastructure\Auth\UserNotFoundException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserNotFoundException
 */
class UserNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function isInstanceOfRuntimeException()
    {
        $exception = new UserNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    /**
     * @test
     */
    public function functionReturnsCorrectInstance()
    {
        $exception = UserNotFoundException::userNotFound('mail');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    /**
     * @test
     */
    public function unableToFindUserMatchingMessage()
    {
        $exception = UserNotFoundException::userNotFound('mail');
        $message   = sprintf('Unable to find a user matching %s', 'mail');

        $this->assertSame($exception->getMessage(), $message);
        $this->assertSame(0, $exception->getCode());
    }
}
