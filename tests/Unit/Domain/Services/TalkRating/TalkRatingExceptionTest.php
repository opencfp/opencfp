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

namespace OpenCFP\Test\Unit\Domain\Services\TalkRating;

use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\TalkRatingException
 */
final class TalkRatingExceptionTest extends Framework\TestCase
{
    public function testIsRuntimeException()
    {
        $exception = new TalkRatingException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testInvalidRatinhReturnsException()
    {
        $rating = 9001;

        $exception = TalkRatingException::invalidRating($rating);

        $this->assertInstanceOf(TalkRatingException::class, $exception);
        $this->assertSame(0, $exception->getCode());

        $message = \sprintf(
            'Invalid talk rating: %s',
            $rating
        );

        $this->assertSame($message, $exception->getMessage());
    }
}
