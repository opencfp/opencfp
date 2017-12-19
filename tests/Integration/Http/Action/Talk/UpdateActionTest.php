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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\Integration\RequiresDatabaseReset;
use OpenCFP\Test\Integration\WebTestCase;

final class UpdateActionTest extends WebTestCase implements RequiresDatabaseReset
{
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
        $this->assertSessionHasFlashMessage('Read Only', $this->session());
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
        $this->assertSessionHasFlashMessage('Error', $this->session());
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
