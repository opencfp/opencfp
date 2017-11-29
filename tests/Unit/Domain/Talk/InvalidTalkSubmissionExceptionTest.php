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

namespace OpenCFP\Test\Unit\Domain\Talk;

use OpenCFP\Domain\Talk\InvalidTalkSubmissionException;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Talk\InvalidTalkSubmissionException
 */
final class InvalidTalkSubmissionExceptionTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsValidationException()
    {
        $exception = new InvalidTalkSubmissionException();

        $this->assertInstanceOf(ValidationException::class, $exception);
    }

    public function testNoCategoryReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noCategory();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You must choose what category of talk you are submitting.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoValidCategoryReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noValidCategory();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You did not choose a valid talk category.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoDescriptionReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noDescription();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('The description of the talk must be included.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoLevelReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noLevel();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You must choose when level of talk you are submitting.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoValidLevelReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noValidLevel();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You did not choose a valid talk level.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoTalkTypeReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noTalkType();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You must choose what type of talk you are submitting.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoValidTalkTypeReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noValidTalkType();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('You did not choose a valid talk type.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoTitleReturnsException()
    {
        $exception = InvalidTalkSubmissionException::noTitle();

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);
        $this->assertSame('The title of the talk must be provided.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testNoValidTitleReturnsException()
    {
        $maxLength = $this->getFaker()->numberBetween(1);

        $exception = InvalidTalkSubmissionException::noValidTitle($maxLength);

        $this->assertInstanceOf(InvalidTalkSubmissionException::class, $exception);

        $message = \sprintf(
            'The title of the talk must be %d characters or less.',
            $maxLength
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
