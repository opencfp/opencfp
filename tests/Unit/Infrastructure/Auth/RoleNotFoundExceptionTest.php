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

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Localheinz\Test\Util\Helper;
use OpenCFP\Infrastructure\Auth\RoleNotFoundException;

final class RoleNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    public function testIsFinal()
    {
        $this->assertClassIsFinal(RoleNotFoundException::class);
    }

    public function testIsRuntimeException()
    {
        $this->assertClassExtends(\RuntimeException::class, RoleNotFoundException::class);
    }

    public function testFromNameReturnsException()
    {
        $name = $this->faker()->word;

        $exception = RoleNotFoundException::fromName($name);

        $this->assertInstanceOf(RoleNotFoundException::class, $exception);

        $message = \sprintf(
            'Unable to find a role with name "%s".',
            $name
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
