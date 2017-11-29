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
