<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class EditActionTest extends WebTestCase
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
     * @test
     */
    public function canNotEditTalkAfterCfpIsClosed()
    {
        $csrfToken = $this->container->get('security.csrf.token_manager')
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
        $csrfToken = $this->container->get('security.csrf.token_manager')
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
}
