<?php

namespace OpenCFP\Test\Unit\Domain\Services\TalkRating;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\YesNoRating
 */
class YesNoRatingTest extends MockeryTestCase
{
    public function testRateThrowsExceptionOnInvalidRating()
    {
        $mockAuth = m::mock(Authentication::class);
        $metaMock = m::mock(TalkMeta::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $sut = new YesNoRating($metaMock, $mockAuth);

        $this->expectException(TalkRatingException::class);
        $this->expectExceptionMessage('Invalid talk rating: 9001');

        $sut->rate(7, 9001);
    }

    public function testRate()
    {
        $mockAuth = m::mock(Authentication::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $metaMock = m::mock(TalkMeta::class)->makePartial();
        $metaMock->shouldReceive('firstOrCreate')->andReturnSelf();
        $metaMock->shouldReceive('save')->andReturnSelf();

        $sut = new YesNoRating($metaMock, $mockAuth);

        $sut->rate(7, 1);

        $this->assertEquals(1, $metaMock->rating);
    }
}
