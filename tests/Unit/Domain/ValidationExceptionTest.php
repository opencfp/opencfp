<?php

namespace OpenCFP\Test\Unit\Domain;

use OpenCFP\Domain\ValidationException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\ValidationException
 */
final class ValidationExceptionTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsException()
    {
        $exception = new ValidationException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testWithErrorsReturnsException()
    {
        $errors = $this->getFaker()->sentences;

        $exception = ValidationException::withErrors($errors);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame('There was an error.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($errors, $exception->errors());
    }
}
