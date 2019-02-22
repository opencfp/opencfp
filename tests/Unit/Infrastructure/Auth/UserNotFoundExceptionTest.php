<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Localheinz\Test\Util\Helper;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

final class UserNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(UserNotFoundException::class);
    }

    /**
     * @test
     */
    public function isInstanceOfRuntimeException()
    {
        $this->assertClassExtends(\RuntimeException::class, UserNotFoundException::class);
    }

    /**
     * @test
     */
    public function fromEmailReturnsException()
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

    /**
     * @test
     */
    public function fromIdReturnsException()
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
