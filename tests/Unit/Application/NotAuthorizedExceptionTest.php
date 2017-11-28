<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Application;

use OpenCFP\Application\NotAuthorizedException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Application\NotAuthorizedException
 */
final class NotAuthorizedExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new NotAuthorizedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
