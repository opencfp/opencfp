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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class EditActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function canNotEditTalkAfterCfpIsClosed()
    {
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = $talk->speaker()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')->getToken('edit_talk');

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsClosed()
            ->get('/talk/edit/' . $talk->id . '?token_id=edit_talk&token=' . $csrfToken);

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
        /** @var User $speaker*/
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/talk/edit/a');

        $this->assertResponseBodyNotContains('Edit Your Talk', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardWhenTalkIsNotYours()
    {
        /** @var User $otherSpeaker */
        $otherSpeaker = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($otherSpeaker->id)
            ->get('talk/edit/' . $talk->id);

        $this->assertResponseBodyNotContains('Edit Your Talk', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create(['user_id' => $speaker->id])->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('edit_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsOpen()
            ->get('/talk/edit/' . $talk->id . '?token_id=edit_talk&token=' . $csrfToken);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($talk->title, $response);
        $this->assertResponseBodyContains('Edit Your Talk', $response);
    }

    /**
     * @test
     */
    public function cannotEditTalkWithBadToken()
    {
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = $talk->speaker->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/talk/edit/' . $talk->id . '?token_id=edit_talk&token=' . \uniqid());

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
