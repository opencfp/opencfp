<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Services;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services\AuthenticationException;
use PHPUnit\Framework;

final class AuthenticationExceptionTest extends Framework\TestCase
{
    use Helper;

    public function testIsFinal()
    {
        $this->assertClassIsFinal(AuthenticationException::class);
    }

    public function testIsRuntimeException()
    {
        $this->assertClassExtends(\RuntimeException::class, AuthenticationException::class);
    }

    public function testLoginFailureHasCorrectMessage()
    {
        $exception = AuthenticationException::loginFailure();

        $this->assertSame('Failure to login.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testLoginFailureReturnsAnAuthenticationException()
    {
        $exception = AuthenticationException::loginFailure();
        $this->assertInstanceOf(AuthenticationException::class, $exception);
    }
}
