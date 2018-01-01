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

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class CreateProcessActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function ampersandsAcceptableCharacterForTalks()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('edit_talk');

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'title'       => 'Test Title With Ampersand',
                'description' => 'The title should contain this & that',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'other',
                'desired'     => 0,
                'user_id'     => $user->id,
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkAfterCFPIsClosed()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->callForPapersIsClosed()
            ->post('/talk/create', [
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('You cannot create talks once the call for papers has ended', $this->session());
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkWithMissingData()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'description' => 'Talk Description',
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('Error', $this->session());
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
}
