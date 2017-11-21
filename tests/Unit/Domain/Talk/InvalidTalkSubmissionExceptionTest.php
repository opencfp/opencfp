<?php

namespace OpenCFP\Test\Unit\Domain\Talk;

use OpenCFP\Domain\Talk\InvalidTalkSubmissionException;
use OpenCFP\Domain\ValidationException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Talk\InvalidTalkSubmissionException
 */
final class InvalidTalkSubmissionExceptionTest extends Framework\TestCase
{
    public function testIsValidationException()
    {
        $exception = new InvalidTalkSubmissionException();

        $this->assertInstanceOf(ValidationException::class, $exception);
    }
}
