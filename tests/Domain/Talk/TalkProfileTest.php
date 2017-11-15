<?php

namespace OpenCFP\Test\Domain\Talk;

use Cartalyst\Sentry\Users\UserInterface;
use Illuminate\Support\Collection;
use Mockery;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkProfile;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

class TalkProfileTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @var Talk
     */
    private static $talk;

    private $identity;

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

    public function setUp()
    {
        parent::setUp();
        $user     = Mockery::mock(UserInterface::class);
        $user->id = 1;
        $provider = Mockery::mock(IdentityProvider::class);
        $provider->shouldReceive('getCurrentUser')->andReturn($user);
        $this->identity = $provider;
    }

    /**
     * @test
     */
    public function getSpeakerReturnsSpeakerProfile()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $speaker = $talkProfile->getSpeaker();
        $this->assertInstanceOf(SpeakerProfile::class, $speaker);
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->title, $talkProfile->with(self::$talk)->getTitle());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsDescription()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->description, $talkProfile->with(self::$talk)->getDescription());
    }

    /**
     * @test
     */
    public function getOtherReturnsOther()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->other, $talkProfile->with(self::$talk)->getOther());
    }

    /**
     * @test
     */
    public function getTypeReturnsType()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->type, $talkProfile->with(self::$talk)->getType());
    }

    /**
     * @test
     */
    public function getLevelReturnsLevel()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->level, $talkProfile->with(self::$talk)->getLevel());
    }

    /**
     * @test
     */
    public function getCategoryReturnsCategory()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->category, $talkProfile->with(self::$talk)->getCategory());
    }

    /**
     * @test
     */
    public function getSlidesReturnsSlides()
    {
        $talkProfile = new TalkProfile($this->identity);
        $this->assertSame(self::$talk->slides, $talkProfile->with(self::$talk)->getSlides());
    }

    /**
     * @test
     */
    public function isDesiredReturnsBool()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $isDesired = $talkProfile->isDesired();
        $this->assertFalse($isDesired);
    }

    /**
     * @test
     */
    public function isSponsorReturnsBool()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $isSponsor = $talkProfile->isSponsor();
        $this->assertTrue($isSponsor);
    }

    /**
     * @test
     */
    public function isSpeakerFavoriteReturnsBool()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $isSpeakerFavorite = $talkProfile->isSpeakerFavorite();
        $this->assertTrue($isSpeakerFavorite);
    }

    /**
     * @test
     */
    public function isSelectedReturnsBool()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $isSelected = $talkProfile->isSelected();
        $this->assertFalse($isSelected);
    }

    /**
     * @test
     */
    public function getCommentsReturnsComments()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $comments = $talkProfile->getComments();
        $this->assertInstanceOf(Collection::class, $comments);
        //The talk has no comments so it returns 0.
        $this->assertCount(0, $comments);
    }

    /**
     * @test
     */
    public function getRatingReturnsZeroWhenNoRatingIsSetByUser()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $this->assertSame(0, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function isViewedReturnsFalseWhenNoMetaIsSetForUser()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $this->assertFalse($talkProfile->isViewed());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsFalseWhenNoFavoriteSet()
    {
        $talkProfile = new TalkProfile($this->identity);
        $talkProfile->with(self::$talk);
        $this->assertFalse($talkProfile->isMyFavorite());
    }
}
