<?php

namespace OpenCFP\Test\Unit\Domain\Services;

use OpenCFP\Domain\Services\NotAuthenticatedException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Services\NotAuthenticatedException
 */
final class NotAuthenticatedExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new NotAuthenticatedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
