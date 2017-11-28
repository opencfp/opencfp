<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use OpenCFP\Infrastructure\Auth\UserNotFoundException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserNotFoundException
 */
class UserNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(UserNotFoundException::class);
        $this->assertTrue($reflection->isFinal());
    }

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
        $message   = \sprintf('Unable to find a user matching %s', 'mail');

        $this->assertSame($exception->getMessage(), $message);
        $this->assertSame(0, $exception->getCode());
    }
}
