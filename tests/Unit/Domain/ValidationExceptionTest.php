<?php

namespace OpenCFP\Test\Unit\Domain;

use OpenCFP\Domain\ValidationException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\ValidationException
 */
final class ValidationExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new ValidationException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
