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

namespace OpenCFP\Test\Integration\Http\Controller;

use Mockery as m;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * @group db
 * @coversNothing
 */
final class TalkControllerTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private static $user;

    /**
     * @var Talk
     */
    private static $talk;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $talk       = factory(Talk::class, 1)->create()->first();
        self::$user = $talk->speaker->first();
        self::$talk = $talk;
    }

    /**
     * Verify that talks with ampersands and other characters in them can
     * be created and then edited properly
     *
     * @test
     */
    public function ampersandsAcceptableCharacterForTalks()
    {
        // Create a test double for SwiftMailer
        $swiftMailer = m::mock(\Swift_Mailer::class);
        $swiftMailer->shouldReceive('send')->andReturn(true);
        $this->swap('mailer', $swiftMailer);

        // Get our request object to return expected data
        $talkData = [
            'title'       => 'Test Title With Ampersand',
            'description' => 'The title should contain this & that',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'other',
            'desired'     => 0,
            'user_id'     => 1,
        ];

        $this->asLoggedInSpeaker(1)
            ->callForPapersIsOpen()
            ->passCsrfValidator()
            ->post('/talk/create', $talkData)
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function allowSubmissionsUntilRightBeforeMidnightDayOfClose()
    {
        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new \DateTime();
        $this->swap(CallForPapers::class, new CallForPapers(new \DateTimeImmutable($now->format('M. jS, Y'))));

        /*
         * This should not have a flash message. The fact that this
         * is true means code is working as intended. Previously this fails
         * because the CFP incorrectly ended at 12:00am the day of, not 11:59pm.
         */
        $this->asLoggedInSpeaker()
            ->get('/talk/create')
            ->assertSee('Create Your Talk');
    }

    /**
     * @test
     */
    public function willDisplayOwnTalk()
    {
        $speakers = m::mock(Speakers::class);
        $speakers->shouldReceive('getTalk')->andReturn(self::$talk);
        $this->swap('application.speakers', $speakers);

        $this->asLoggedInSpeaker((int) self::$user->id)
            ->get('/talk/view' . self::$talk->id)
            ->assertSee(self::$talk->title)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function canNotEditTalkAfterCfpIsClosed()
    {
        $this->asLoggedInSpeaker(self::$user->id)
            ->callForPapersIsClosed()
            ->passCsrfValidator()
            ->get('/talk/edit/' . self::$talk->id)
            ->assertFlashContains('error')
            ->assertFlashContains('You cannot edit talks once the call for papers has ended')
            ->assertNotSee('Edit Your Talk')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardOnEditWhenNoTalkID()
    {
        $this->asLoggedInSpeaker()
            ->get('/talk/edit/a')
            ->assertNotSee('Edit Your Talk')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardWhenTalkIsNotYours()
    {
        $this->asLoggedInSpeaker(self::$user->id + 1)
            ->get('talk/edit/' . self::$talk->id)
            ->assertNotSee('Edit Your Talk')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('edit_talk')
            ->getValue();
        $this->asLoggedInSpeaker(self::$user->id)
            ->get('/talk/edit/' . self::$talk->id . '?token_id=edit_talk&token=' . $csrfToken)
            ->assertSee(self::$talk->title)
            ->assertSee('Edit Your Talk')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function cannotEditTalkWithBadToken()
    {
        $this->asLoggedInSpeaker(self::$user->id)
            ->get('/talk/edit/' . self::$talk->id . '?token_id=edit_talk&token=' . \uniqid())
            ->assertRedirect()
            ->assertTargetURLContains('/dashboard');
    }

    /**
     * @test
     */
    public function notAllowedToDeleteAfterCFPIsOver()
    {
        $this->asLoggedInSpeaker(self::$user->id)
            ->callForPapersIsClosed()
            ->passCsrfValidator()
            ->post('/talk/delete', ['tid' => self::$talk->id])
            ->assertNotSee('ok')
            ->assertSee('no')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function notAllowedToDeleteSomeoneElseTalk()
    {
        $this->asLoggedInSpeaker(self::$user->id + 1)
            ->passCsrfValidator()
            ->post('/talk/delete', ['tid' => self::$talk->id])
            ->assertNotSee('ok')
            ->assertSee('no')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function cantCreateTalkAfterCFPIsClosed()
    {
        $this->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->get('/talk/create')
            ->assertRedirect()
            ->assertFlashContains('You cannot create talks once the call for papers has ended')
            ->assertNotSee('Create Your Talk');
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkAfterCFPIsClosed()
    {
        $this->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->passCsrfValidator()
            ->post('/talk/create')
            ->assertRedirect()
            ->assertFlashContains('You cannot create talks once the call for papers has ended')
            ->assertNotSee('Create Your Talk');
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkWithMissingData()
    {
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('speaker_talk')
            ->getValue();
        $postData = [
            'description' => 'Talk Description',
            'token'       => $csrfToken,
            'token_id'    => 'speaker_talk',
        ];
        $this->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->passCsrfValidator()
            ->post('/talk/create', $postData)
            ->assertSuccessful()
            ->assertSee('Create Your Talk')
            ->assertFlashContains('Error');
    }

    /**
     * @test
     */
    public function processCreateTalkFailsWithBadToken()
    {
        $postData = [
            'description' => 'Talk Description',
            'token'       => \uniqid(),
            'token_id'    => 'speaker_talk',
        ];
        $this->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/create', $postData)
            ->assertRedirect()
            ->assertTargetURLContains('/dashboard');
    }

    /**
     * @test
     */
    public function cantUpdateActionAFterCFPIsClosed()
    {
        $this->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->passCsrfValidator()
            ->post('/talk/update', ['id' => 2])
            ->assertFlashContains('Read Only')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function cantUpdateActionWithInvalidData()
    {
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('speaker_talk')
            ->getValue();
        $postData = [
            'id'       => 2,
            'token'    => $csrfToken,
            'token_id' => 'speaker_talk',
        ];
        $this->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/update', $postData)
            ->assertFlashContains('Error')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function cantUpdateActionWithBadToken()
    {
        $postData = [
            'id'       => 2,
            'token'    => \uniqid(),
            'token_id' => 'speaker_talk',
        ];
        $this->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/update', $postData)
            ->assertRedirect()
            ->assertTargetURLContains('/dashboard');
    }
}
