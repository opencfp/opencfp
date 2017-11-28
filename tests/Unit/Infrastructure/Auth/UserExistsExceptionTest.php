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

use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

/**
 * @covers \OpenCFP\Infrastructure\Auth\UserExistsException
 */
class UserExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    public function testItIsTheCorrectTypeOfException()
    {
        $exception = new UserExistsException();
        $this->assertInstanceOf(\UnexpectedValueException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame(0, $exception->getCode());
    }

    public function testFromEmailReturnsException()
    {
        $email = $this->getFaker()->email;

        $exception = UserExistsException::fromEmail($email);

        $this->assertInstanceOf(UserExistsException::class, $exception);

        $message = \sprintf(
            'A user with the email address "%s" already exists.',
            $email
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
