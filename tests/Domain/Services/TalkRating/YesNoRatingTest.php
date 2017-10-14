<?php


namespace OpenCFP\Test\Domain\Services\TalkRating;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OpenCFP\Domain\Entity\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;
use Spot\MapperInterface;

class YesNoRatingTest extends MockeryTestCase
{
    public function testRateThrowsExceptionOnInvalidRating()
    {
        $mockAuth = m::mock(Authentication::class);
        $mockMapper = m::mock(MapperInterface::class);

        $sut = new YesNoRating($mockMapper, $mockAuth);

        $this->expectException(TalkRatingException::class);
        $this->expectExceptionMessage('Invalid talk rating: 9001');

        $sut->rate(7, 9001);
    }

    public function testRate()
    {
        $talkMeta = new TalkMeta();

        $mockAuth = m::mock(Authentication::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $mockMapper = m::mock(MapperInterface::class);
        $mockMapper->shouldReceive('where->first')->andReturn(false);
        $mockMapper->shouldReceive('get')->andReturn($talkMeta);
        $mockMapper->shouldReceive('save')->withArgs([$talkMeta]);

        $sut = new YesNoRating($mockMapper, $mockAuth);

        $sut->rate(7, 1);

        self::assertEquals(1, $talkMeta->admin_user_id);
        self::assertEquals(7, $talkMeta->talk_id);
        self::assertEquals(1, $talkMeta->rating);
    }

    public function testRerate()
    {
        $talkMeta = new TalkMeta();
        $talkMeta->rating = 0;

        $mockAuth = m::mock(Authentication::class);
        $mockAuth->shouldReceive('userId')->andReturn(1);

        $mockMapper = m::mock(MapperInterface::class);
        $mockMapper->shouldReceive('where->first')->andReturn($talkMeta);
        $mockMapper->shouldReceive('save')->withArgs([$talkMeta]);

        $sut = new YesNoRating($mockMapper, $mockAuth);

        $sut->rate(7, 1);

        self::assertEquals(1, $talkMeta->rating);
    }
}
