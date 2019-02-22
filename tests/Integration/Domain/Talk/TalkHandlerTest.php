<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Domain\Talk;

use Mockery;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Domain\Talk\TalkProfile;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkHandlerTest extends WebTestCase implements TransactionalTestCase
{
    private $authentication;

    private $ratingSystem;

    protected function setUp()
    {
        parent::setUp();

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(1);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('user')->andReturn($user);
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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $this->assertSame(0, $talk->selected);

        $this->assertTrue($talkHandler->select());
        $this->assertSame(1, $talk->selected);

        $this->assertTrue($talkHandler->select(false));
        $this->assertSame(0, $talk->selected);
    }

    /**
     * @test
     */
    public function favoriteCreatesAndDeletesFavorites()
    {
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        //Check we have no favorites on this talk.
        $this->assertCount(0, $talk->favorites()->get());
        $this->assertTrue($talkHandler->setFavorite());
        //The handler should have favorited the talk now.
        $favorite = $talk->favorites()->get();
        $this->assertCount(1, $favorite);
        $this->assertSame(1, $favorite->first()->admin_user_id);

        //Calling favorite again doesn't do anything
        $this->assertTrue($talkHandler->setFavorite());
        $favoriteAgain = $talk->favorites()->get();
        $this->assertCount(1, $favoriteAgain);
        $this->assertSame(1, $favoriteAgain->first()->admin_user_id);

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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

        $ratingSystem = Mockery::mock(TalkRatingStrategy::class);
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
        $talk = $talk = Mockery::mock(Talk::class)->makePartial();
        $talk->shouldReceive('getMetaFor')->andReturnSelf();
        $talk->shouldReceive('save')->andReturn(true);
        $talk->viewed = 0;
        $talkHandler  = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        $this->assertTrue($talkHandler->view());
        $this->assertSame(1, $talk->viewed);
        //Calling it again doesn't do anything funky
        $this->assertTrue($talkHandler->view());
        $this->assertSame(1, $talk->viewed);
    }

    /**
     * @test
     */
    public function viewedWillReturnFalseOnError()
    {
        $talk = $talk = Mockery::mock(Talk::class)->makePartial();
        $talk->shouldReceive('getMetaFor')->andThrow(\Exception::class);
        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);

        $this->assertFalse($talkHandler->view());
        $this->assertNotSame(1, $talk->viewed);
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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $this->assertInstanceOf(TalkHandler::class, $talkHandler->grabTalk((int) $talk->id));
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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['selected' => 0])->first();

        $talkHandler = new TalkHandler($this->authentication, $this->ratingSystem);
        $talkHandler->with($talk);
        $profile = $talkHandler->getProfile();
        $this->assertInstanceOf(TalkProfile::class, $profile);
        //Check the talk got set correctly in the profile.
        $this->assertSame($talk->title, $profile->getTitle());
    }
}
