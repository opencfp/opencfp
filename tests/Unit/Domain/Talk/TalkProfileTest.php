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

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mockery as m;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkProfile;

/**
 * @covers \OpenCFP\Domain\Talk\TalkProfile
 */
class TalkProfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getSpeakerReturnsSpeakerProfile()
    {
        $user          = m::mock(User::class);
        $talk          = m::mock(Talk::class)->makePartial()->makePartial();
        $talk->speaker = $user;
        $talkProfile   = new TalkProfile($talk);
        $speaker       = $talkProfile->getSpeaker();
        $this->assertInstanceOf(SpeakerProfile::class, $speaker);
    }

    /**
     * @test
     */
    public function getIdReturnsId()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->id    = 2;
        $talkProfile = new TalkProfile($talk);
        $this->assertEquals($talk->id, $talkProfile->getId());
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->title = 'Title';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->title, $talkProfile->getTitle());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsDescription()
    {
        $talk              = m::mock(Talk::class)->makePartial();
        $talk->description = 'Describe the talk';
        $talkProfile       = new TalkProfile($talk);
        $this->assertSame($talk->description, $talkProfile->getDescription());
    }

    /**
     * @test
     */
    public function getOtherReturnsOther()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->other = 'Other information';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->other, $talkProfile->getOther());
    }

    /**
     * @test
     */
    public function getTypeReturnsType()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->type  = 'api';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->type, $talkProfile->getType());
    }

    /**
     * @test
     */
    public function getLevelReturnsLevel()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->level = 'starter';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->level, $talkProfile->getLevel());
    }

    /**
     * @test
     */
    public function getCategoryReturnsCategory()
    {
        $talk           = m::mock(Talk::class)->makePartial();
        $talk->category = 'blep';
        $talkProfile    = new TalkProfile($talk);
        $this->assertSame($talk->category, $talkProfile->getCategory());
    }

    /**
     * @test
     */
    public function getSlidesReturnsSlides()
    {
        $talk         = m::mock(Talk::class)->makePartial();
        $talk->slides = 'http://www.example.com/slides.png';
        $talkProfile  = new TalkProfile($talk);
        $this->assertSame($talk->slides, $talkProfile->getSlides());
    }

    /**
     * @test
     */
    public function isDesiredReturnsBool()
    {
        $talk          = m::mock(Talk::class)->makePartial();
        $talk->desired = 0;
        $talkProfile   = new TalkProfile($talk);
        $isDesired     = $talkProfile->isDesired();
        $this->assertFalse($isDesired);
    }

    /**
     * @test
     */
    public function isSponsorReturnsBool()
    {
        $talk          = m::mock(Talk::class)->makePartial();
        $talk->sponsor = 1;
        $talkProfile   = new TalkProfile($talk);
        $isSponsor     = $talkProfile->isSponsor();
        $this->assertTrue($isSponsor);
    }

    /**
     * @test
     */
    public function isSpeakerFavoriteReturnsBool()
    {
        $talk              = m::mock(Talk::class)->makePartial();
        $talk->favorite    = 1;
        $talkProfile       = new TalkProfile($talk);
        $isSpeakerFavorite = $talkProfile->isSpeakerFavorite();
        $this->assertTrue($isSpeakerFavorite);
    }

    /**
     * @test
     */
    public function isSelectedReturnsBool()
    {
        $talk           = m::mock(Talk::class)->makePartial();
        $talk->selected = 0;
        $talkProfile    = new TalkProfile($talk);
        $isSelected     = $talkProfile->isSelected();
        $this->assertFalse($isSelected);
    }

    /**
     * @test
     */
    public function getCommentsReturnsComments()
    {
        $many = m::mock(HasMany::class);
        $many->shouldReceive('get')->andReturn(collect());
        $talk = m::mock(Talk::class)->makePartial();
        $talk->shouldReceive('comments')->andReturn($many);
        $talkProfile = new TalkProfile($talk);
        $comments    = $talkProfile->getComments();
        $this->assertInstanceOf(Collection::class, $comments);
        //The talk has no comments so it returns 0.
        $this->assertCount(0, $comments);
    }

    /**
     * @test
     */
    public function getRatingReturnsZeroWhenNoRatingIsSetByUser()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talkProfile = new TalkProfile($talk);
        $this->assertSame(0, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function isViewedReturnsFalseWhenNoMetaIsSetForUser()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talkProfile = new TalkProfile($talk);
        $this->assertFalse($talkProfile->isViewedByMe());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsFalseWhenNoFavoriteSet()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talkProfile = new TalkProfile($talk);
        $this->assertFalse($talkProfile->isMyFavorite());
    }
}
