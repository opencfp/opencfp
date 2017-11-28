<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Talk;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkFormatter;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Talk\TalkFilter
 */
class TalkFilterTest extends Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    protected $talk;

    protected $formatter;

    protected function setUp()
    {
        parent::setUp();
        $this->talk      = Mockery::mock(Talk::class);
        $this->formatter = Mockery::mock(TalkFormatter::class);
    }

    /**
     * @test
     */
    public function getFilteredTalksWithoutAFilterReturnsTheTalksObject()
    {
        $filter = new TalkFilter(new TalkFormatter(), $this->talk);
        $talk   = $filter->getFilteredTalks(1);
        $this->assertEquals($this->talk, $talk);
    }

    /**
     * @test
     */
    public function getFilteredTalksWithANonsenseFilterReturnsTheTalkObject()
    {
        $filter = new TalkFilter(new TalkFormatter(), $this->talk);
        $talk   = $filter->getFilteredTalks(1, 'secrets');
        $this->assertEquals($this->talk, $talk);
    }

    /**
     * @test
     */
    public function getFilteredTalksWithWronglyCasedFilterWorksCorrectly()
    {
        $this->talk->shouldReceive('selected')->andReturn('gotSelected');
        $filter = new TalkFilter(new TalkFormatter(), $this->talk);
        $talk   = $filter->getFilteredTalks(1, 'selEcteD');
        $this->assertEquals('gotSelected', $talk);
    }

    /**
     * @test
     */
    public function getFilteredTalksWithNormalCasedFilterWorksCorrectly()
    {
        $this->talk->shouldReceive('topRated')->andReturn('gotTopRated');
        $filter = new TalkFilter(new TalkFormatter(), $this->talk);
        $talk   = $filter->getFilteredTalks(1, 'toprated');
        $this->assertEquals('gotTopRated', $talk);
    }

    /**
     * @test
     */
    public function getFilteredTalksWorksWithOtherOptions()
    {
        $this->talk->shouldReceive('notRatedBy')->andReturn('gotnotrated');
        $this->talk->shouldReceive('ratedPlusOneBy')->andReturn('gotplusone');
        $this->talk->shouldReceive('viewedBy')->andReturn('gotviewed');
        $this->talk->shouldReceive('favoritedBy')->andReturn('gotfavorited');
        $filter = new TalkFilter(new TalkFormatter(), $this->talk);
        $talk   = $filter->getFilteredTalks(1, 'notrated');
        $this->assertEquals('gotnotrated', $talk);
        $talk = $filter->getFilteredTalks(1, 'plusone');
        $this->assertEquals('gotplusone', $talk);
        $talk = $filter->getFilteredTalks(1, 'viewed');
        $this->assertEquals('gotviewed', $talk);
        $talk = $filter->getFilteredTalks(1, 'favorited');
        $this->assertEquals('gotfavorited', $talk);
    }

    /**
     * @test
     */
    public function getTalksAttemptsToFormatTheTalks()
    {
        $this->talk->shouldReceive('notViewedBy->orderBy->get')->andReturn(collect());
        $this->formatter->shouldReceive('formatList')->andReturn(collect(['Got an Array']));

        $filter = new TalkFilter($this->formatter, $this->talk);

        $return = $filter->getTalks(1, 'notviewed');

        $this->assertContains('Got an Array', $return);
    }
}
