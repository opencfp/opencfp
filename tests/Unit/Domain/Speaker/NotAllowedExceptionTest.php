<?php

namespace OpenCFP\Test\Unit\Domain\Speaker;

use OpenCFP\Domain\Speaker\NotAllowedException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Speaker\NotAllowedException
 */
final class NotAllowedExceptionTest extends Framework\TestCase
{
    public function testIsException()
    {
        $exception = new NotAllowedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testNotAllowedToViewReturnsException()
    {
        $property = 'foo';

        $exception = NotAllowedException::notAllowedToView($property);

        $this->assertInstanceOf(NotAllowedException::class, $exception);
        $this->assertSame(0, $exception->getCode());

        $message = sprintf(
            'Not allowed to view %s. Hidden property',
            $property
        );

        $this->assertSame($message, $exception->getMessage());
    }
}
