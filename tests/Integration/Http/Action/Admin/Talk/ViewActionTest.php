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

namespace OpenCFP\Test\Integration\Http\Action\Admin\Talk;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ViewActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * Verify that not found talk redirects
     *
     * @test
     */
    public function talkNotFoundRedirectsBackToTalksOverview()
    {
        $id = $this->faker()->numberBetween(1);

        $response = $this->get('/admin/talks/' . $id);

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('<strong>Submitted by:</strong>', $response);
    }

    /**
     * @test
     */
    public function talkWithNoMetaDisplaysCorrectly()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        /** @var Model\Talk $talk */
        $talk = factory(Model\Talk::class)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/talks/' . $talk->id);

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function previouslyViewedTalksDisplaysCorrectly()
    {
        /** @var Model\TalkMeta $talkMeta */
        $talkMeta = factory(Model\TalkMeta::class)->create()->first();

        $response = $this
            ->asAdmin($talkMeta->admin_user_id)
            ->get('/admin/talks/' . $talkMeta->talk_id);

        $this->assertResponseIsSuccessful($response);
    }
}
