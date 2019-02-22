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

namespace OpenCFP\Test\Unit\Domain\Services;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services\AuthenticationException;
use PHPUnit\Framework;

final class AuthenticationExceptionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(AuthenticationException::class);
    }

    /**
     * @test
     */
    public function isRuntimeException()
    {
        $this->assertClassExtends(\RuntimeException::class, AuthenticationException::class);
    }

    /**
     * @test
     */
    public function loginFailureHasCorrectMessage()
    {
        $exception = AuthenticationException::loginFailure();

        $this->assertSame('Failure to login.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * @test
     */
    public function loginFailureReturnsAnAuthenticationException()
    {
        $exception = AuthenticationException::loginFailure();
        $this->assertInstanceOf(AuthenticationException::class, $exception);
    }
}
