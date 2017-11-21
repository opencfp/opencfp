<?php

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
