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

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

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
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('edit_talk');
        $response = $this
            ->asLoggedInSpeaker(1)
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'title'       => 'Test Title With Ampersand',
                'description' => 'The title should contain this & that',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'other',
                'desired'     => 0,
                'user_id'     => 1,
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
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

        $cfp    = $this->container->get(CallForPapers::class);
        $method = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($cfp, new \DateTimeImmutable($now->format('M. jS, Y')));

        $this->container->get('twig')->addGlobal('cfp_open', $cfp->isOpen());

        /*
         * This should not have a flash message. The fact that this
         * is true means code is working as intended. Previously this fails
         * because the CFP incorrectly ended at 12:00am the day of, not 11:59pm.
         */
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/talk/create');

        $this->assertResponseBodyContains('Create Your Talk', $response);
    }
    
    /**
     * @test
     */
    public function canNotEditTalkAfterCfpIsClosed()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('edit_talk');

        $response = $this
            ->asLoggedInSpeaker(self::$user->id)
            ->callForPapersIsClosed()
            ->get('/talk/edit/' . self::$talk->id . '?token_id=edit_talk&token=' . $csrfToken);

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Edit Your Talk', $response);
        $this->assertSessionHasFlashMessage('error', $this->container->get('session'));
        $this->assertSessionHasFlashMessage('You cannot edit talks once the call for papers has ended', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardOnEditWhenNoTalkID()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/talk/edit/a');

        $this->assertResponseBodyNotContains('Edit Your Talk', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardWhenTalkIsNotYours()
    {
        $response = $this
            ->asLoggedInSpeaker(self::$user->id + 1)
            ->get('talk/edit/' . self::$talk->id);

        $this->assertResponseBodyNotContains('Edit Your Talk', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('edit_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker(self::$user->id)
            ->get('/talk/edit/' . self::$talk->id . '?token_id=edit_talk&token=' . $csrfToken);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$talk->title, $response);
        $this->assertResponseBodyContains('Edit Your Talk', $response);
    }

    /**
     * @test
     */
    public function cannotEditTalkWithBadToken()
    {
        $response = $this
            ->asLoggedInSpeaker(self::$user->id)
            ->get('/talk/edit/' . self::$talk->id . '?token_id=edit_talk&token=' . \uniqid());

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }

    /**
     * @test
     */
    public function notAllowedToDeleteAfterCFPIsOver()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker(self::$user->id)
            ->callForPapersIsClosed()
            ->post('/talk/delete', [
                'tid'      => self::$talk->id,
                'token'    => $csrfToken,
                'token_id' => 'delete_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('ok', $response);
        $this->assertResponseBodyContains('no', $response);
    }

    /**
     * @test
     */
    public function notAllowedToDeleteSomeoneElseTalk()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker(self::$user->id + 1)
            ->post('/talk/delete', [
                'tid'      => self::$talk->id,
                'token'    => $csrfToken,
                'token_id' => 'delete_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('ok', $response);
        $this->assertResponseBodyContains('no', $response);
    }

    /**
     * @test
     */
    public function cantCreateTalkAfterCFPIsClosed()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->get('/talk/create');

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('You cannot create talks once the call for papers has ended', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkAfterCFPIsClosed()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->post('/talk/create', [
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('You cannot create talks once the call for papers has ended', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkWithMissingData()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'description' => 'Talk Description',
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('Error', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function processCreateTalkFailsWithBadToken()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'description' => 'Talk Description',
                'token'       => \uniqid(),
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }

    /**
     * @test
     */
    public function cantUpdateActionAFterCFPIsClosed()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->post('/talk/update', [
                'id'       => 2,
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertSessionHasFlashMessage('Read Only', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function cantUpdateActionWithInvalidData()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/update', [
                'id'       => 2,
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasFlashMessage('Error', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function cantUpdateActionWithBadToken()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/update', [
                'id'       => 2,
                'token'    => \uniqid(),
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
