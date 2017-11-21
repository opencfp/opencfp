<?php

namespace OpenCFP\Test\Unit\Domain;

use OpenCFP\Domain\EntityNotFoundException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\EntityNotFoundException
 */
final class EntityNotFoundExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new EntityNotFoundException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
