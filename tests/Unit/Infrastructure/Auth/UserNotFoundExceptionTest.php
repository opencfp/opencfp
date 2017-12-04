<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Localheinz\Test\Util\Helper;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserNotFoundException
 */
class UserNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

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

    public function testFromEmailReturnsException()
    {
        $email = $this->faker()->email;

        $exception = UserNotFoundException::fromEmail($email);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);

        $message = \sprintf(
            'Unable to find a user with email "%s".',
            $email
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testFromIdReturnsException()
    {
        $id = $this->faker()->numberBetween(1);

        $exception = UserNotFoundException::fromId($id);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);

        $message = \sprintf(
            'Unable to find a user with id "%d".',
            $id
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
