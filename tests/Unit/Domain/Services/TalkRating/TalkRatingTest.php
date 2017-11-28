<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Services\TalkRating;

use Mockery;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;

/**
 * We Use the YesNoRating class to test the base class, since we know exactly what values are allowed
 *
 * @covers \OpenCFP\Domain\Services\TalkRating\TalkRating
 */
class TalkRatingTest extends \PHPUnit\Framework\TestCase
{
    public function testRateThrowsExceptionOnInvalidRating()
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $metaMock = Mockery::mock(TalkMeta::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $sut = new YesNoRating($metaMock, $mockAuth);

        $this->expectException(TalkRatingException::class);
        $this->expectExceptionMessage('Invalid talk rating: 9001');

        $sut->rate(7, 9001);
    }

    public function testRate()
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $metaMock = Mockery::mock(TalkMeta::class)->makePartial();
        $metaMock->shouldReceive('firstOrCreate')->andReturnSelf();
        $metaMock->shouldReceive('save');

        $sut = new YesNoRating($metaMock, $mockAuth);

        $sut->rate(7, 1);

        $this->assertEquals(1, $metaMock->rating);
    }
}
