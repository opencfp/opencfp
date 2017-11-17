<?php

namespace OpenCFP\Test\Domain\Talk;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkProfile;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * @covers \OpenCFP\Domain\Talk\TalkProfile
 */
class TalkProfileTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @var Talk
     */
    private static $talk;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$talk = factory(Talk::class, 1)->create([
            'desired'  => 0,
            'sponsor'  => 1,
            'favorite' => 1,
            'selected' => 0,
        ])->first();
    }

    /**
     * @test
     */
    public function getSpeakerReturnsSpeakerProfile()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $speaker     = $talkProfile->getSpeaker();
        $this->assertInstanceOf(SpeakerProfile::class, $speaker);
    }

    /**
     * @test
     */
    public function getIdReturnsId()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertEquals(self::$talk->id, $talkProfile->getId());
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->title, $talkProfile->getTitle());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsDescription()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->description, $talkProfile->getDescription());
    }

    /**
     * @test
     */
    public function getOtherReturnsOther()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->other, $talkProfile->getOther());
    }

    /**
     * @test
     */
    public function getTypeReturnsType()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->type, $talkProfile->getType());
    }

    /**
     * @test
     */
    public function getLevelReturnsLevel()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->level, $talkProfile->getLevel());
    }

    /**
     * @test
     */
    public function getCategoryReturnsCategory()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->category, $talkProfile->getCategory());
    }

    /**
     * @test
     */
    public function getSlidesReturnsSlides()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(self::$talk->slides, $talkProfile->getSlides());
    }

    /**
     * @test
     */
    public function isDesiredReturnsBool()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $isDesired   = $talkProfile->isDesired();
        $this->assertFalse($isDesired);
    }

    /**
     * @test
     */
    public function isSponsorReturnsBool()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $isSponsor   = $talkProfile->isSponsor();
        $this->assertTrue($isSponsor);
    }

    /**
     * @test
     */
    public function isSpeakerFavoriteReturnsBool()
    {
        $talkProfile       = new TalkProfile(self::$talk);
        $isSpeakerFavorite = $talkProfile->isSpeakerFavorite();
        $this->assertTrue($isSpeakerFavorite);
    }

    /**
     * @test
     */
    public function isSelectedReturnsBool()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $isSelected  = $talkProfile->isSelected();
        $this->assertFalse($isSelected);
    }

    /**
     * @test
     */
    public function getCommentsReturnsComments()
    {
        $talkProfile = new TalkProfile(self::$talk);
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
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertSame(0, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function isViewedReturnsFalseWhenNoMetaIsSetForUser()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertFalse($talkProfile->isViewedByMe());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsFalseWhenNoFavoriteSet()
    {
        $talkProfile = new TalkProfile(self::$talk);
        $this->assertFalse($talkProfile->isMyFavorite());
    }
}
