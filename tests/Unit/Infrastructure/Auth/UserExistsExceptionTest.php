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
use OpenCFP\Infrastructure\Auth\UserExistsException;

final class UserExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function itIsTheCorrectTypeOfException()
    {
        $this->assertClassExtends(\UnexpectedValueException::class, UserExistsException::class);
    }

    /**
     * @test
     */
    public function fromEmailReturnsException()
    {
        $email = $this->faker()->email;

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
