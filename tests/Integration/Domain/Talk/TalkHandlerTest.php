<?php

namespace OpenCFP\Test\Integration\Domain\Talk;

use Mockery;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Domain\Talk\TalkProfile;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @coversNothing
 */
class TalkHandlerTest extends BaseTestCase
{
    use RefreshDatabase;

    private static $talk;

    private $authentication;
    private $ratingSystem;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$talk = factory(Talk::class, 1)->create(['selected' => 0])->first();
    }

    protected function setUp()
    {
        parent::setUp();
        $auth     = Mockery::mock(Authentication::class);
        $auth->shouldReceive('userId')->andReturn(1);
        $this->authentication = $auth;
        $ratingSystem         = Mockery::mock(TalkRatingStrategy::class);
        $ratingSystem->shouldReceive('rate');
        $this->ratingSystem = $ratingSystem;
    }

    /**
     * @test
     */
    public function commentOnSetsNewComment()
    {
        $talk        = self::$talk;
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $this->assertTrue($talkHandler->commentOn('Nice Talk!'));
        //Check if its set correctly in DB
        $comment = TalkComment::first();
        $this->assertSame('Nice Talk!', $comment->message);
        $this->assertSame($talk->id, $comment->talk_id);
        $this->assertSame(1, $comment->user_id);
    }

    /**
     * @test
     */
    public function selectSelectsTalk()
    {
        $talk        = self::$talk;
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $this->assertEquals(0, $talk->selected);

        $this->assertTrue($talkHandler->select());
        $this->assertEquals(1, $talk->selected);

        $this->assertTrue($talkHandler->select(false));
        $this->assertEquals(0, $talk->selected);
    }

    /**
     * @test
     */
    public function favoriteCreatesAndDeletesFavorites()
    {
        $talk        = self::$talk;
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        //Check we have no favorites on this talk.
        $this->assertCount(0, $talk->favorites()->get());
        $this->assertTrue($talkHandler->setFavorite());
        //The handler should have favorited the talk now.
        $favorite = $talk->favorites()->get();
        $this->assertCount(1, $favorite);
        $this->assertEquals(1, $favorite->first()->admin_user_id);
        
        //Calling favorite again doesn't do anything
        $this->assertTrue($talkHandler->setFavorite());
        $favoriteAgain = $talk->favorites()->get();
        $this->assertCount(1, $favoriteAgain);
        $this->assertEquals(1, $favoriteAgain->first()->admin_user_id);

        //Now to delete the favorite
        $this->assertTrue($talkHandler->setFavorite(false));
        //The handler should have deleted the favorite of the talk now.
        $favoriteNoMore = $talk->favorites()->get();
        $this->assertCount(0, $favoriteNoMore);

        //Trying to remove it again doesn't do anything funky.
        $this->assertTrue($talkHandler->setFavorite(false));
        //The handler should have deleted the favorite of the talk now.
        $favoriteStillGone = $talk->favorites()->get();
        $this->assertCount(0, $favoriteStillGone);
    }

    /**
     * @test
     */
    public function favoriteReturnsFalseWhenDeleteErrors()
    {
        $talk = Mockery::mock(Talk::class);
        $talk->shouldReceive('favorites')->andThrow(\Exception::class);
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $this->assertFalse($talkHandler->setFavorite(false));
    }

    /**
     * @test
     */
    public function rateReturnsTrueOnSuccess()
    {
        $talk        = self::$talk;
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        // We aren't testing the rating system here, thats its own thing.
        $this->assertTrue($talkHandler->rate(1));
    }

    /**
     * @test
     */
    public function rateReturnsFalseOnError()
    {
        $talk           = self::$talk;
        $ratingSystem   = Mockery::mock(TalkRatingStrategy::class);
        $ratingSystem->shouldReceive('rate')->andThrow(TalkRatingException::class);
        $talkHandler = new TalkHandler($this->authentication, $ratingSystem);
        $talkHandler->with($talk);

        // We aren't testing the rating system here, thats its own thing.
        $this->assertFalse($talkHandler->rate(1));
    }

    /**
     * @test
     */
    public function viewWillSetTheTalkToViewed()
    {
        $talk        = $talk = Mockery::mock(Talk::class)->makePartial();
        $talk->shouldReceive('getMetaFor')->andReturnSelf();
        $talk->shouldReceive('save')->andReturn(true);
        $talk->viewed = 0;
        $talkHandler  = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        $this->assertTrue($talkHandler->view());
        $this->assertEquals(1, $talk->viewed);
        //Calling it again doesn't do anything funky
        $this->assertTrue($talkHandler->view());
        $this->assertEquals(1, $talk->viewed);
    }

    /**
     * @test
     */
    public function viewedWillReturnFalseOnError()
    {
        $talk        = $talk = Mockery::mock(Talk::class)->makePartial();
        $talk->shouldReceive('getMetaFor')->andThrow(\Exception::class);
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        $this->assertFalse($talkHandler->view());
        $this->assertNotEquals(1, $talk->viewed);
    }

    /**
     * @test
     */
    public function allFunctionsReturnFalseWhenNoTalkSet()
    {
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $this->assertFalse($talkHandler->commentOn('blabla'));
        $this->assertFalse($talkHandler->select(true));
        $this->assertFalse($talkHandler->select(false));
        $this->assertFalse($talkHandler->setFavorite(true));
        $this->assertFalse($talkHandler->setFavorite(false));
        $this->assertFalse($talkHandler->rate(0));
        $this->assertFalse($talkHandler->rate(5876987));
        $this->assertFalse($talkHandler->view());
    }

    /**
     * @test
     */
    public function grabTalkSetsTalkWithId()
    {
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $this->assertInstanceOf(TalkHandler::class, $talkHandler->grabTalk((int) self::$talk->id));
        $this->assertTrue($talkHandler->hasTalk());
    }

    /**
     * @test
     */
    public function grabTalkGivesNoErrorsWhenWrongID()
    {
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->grabTalk(45678);

        $this->assertFalse($talkHandler->view());
    }

    /**
     * @test
     */
    public function getProfileReturnsTalkProfile()
    {
        $talk        = self::$talk;
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $profile = $talkHandler->getProfile();
        $this->assertInstanceOf(TalkProfile::class, $profile);
        //Check the talk got set correctly in the profile.
        $this->assertEquals($talk->title, $profile->getTitle());
    }
}
