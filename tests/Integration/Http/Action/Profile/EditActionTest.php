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

namespace OpenCFP\Test\Integration\Http\Action\Profile;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class EditActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function notAbleToSeeEditPageOfOtherPersonsProfile()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        /** @var User $user */
        $otherSpeaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/profile/edit/' . $otherSpeaker->id);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/profile/edit/' . $speaker->id);

        $this->assertResponseIsSuccessful($response);
    }
}
