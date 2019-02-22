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

final class DeleteActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function notAllowedToDeleteAfterCFPIsOver()
    {
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = $talk->speaker()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->callForPapersIsClosed()
            ->post('/talk/delete', [
                'tid'      => $talk->id,
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
        /** @var Talk $talk */
        $talk = factory(Talk::class, 1)->create()->first();

        /** @var User $otherSpeaker*/
        $otherSpeaker = factory(User::class, 1)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker($otherSpeaker->id)
            ->post('/talk/delete', [
                'tid'      => $talk->id,
                'token'    => $csrfToken,
                'token_id' => 'delete_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('ok', $response);
        $this->assertResponseBodyContains('no', $response);
    }
}
