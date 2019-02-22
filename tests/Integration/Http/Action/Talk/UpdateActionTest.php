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

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class UpdateActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function cantUpdateActionAFterCFPIsClosed()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsClosed()
            ->post('/talk/update', [
                'id'       => 2,
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertSessionHasFlashMessage('Read Only', $this->session());
    }

    /**
     * @test
     */
    public function cantUpdateActionWithInvalidData()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsOpen()
            ->post('/talk/update', [
                'id'       => 2,
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasFlashMessage('Error', $this->session());
    }

    /**
     * @test
     */
    public function cantUpdateActionWithBadToken()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
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
