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

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ProfileControllerTest extends WebTestCase implements TransactionalTestCase
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

    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        /** @var User $otherSpeaker */
        $otherSpeaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id' => $otherSpeaker->id,
            ]);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id'         => $speaker->id,
                'email'      => $this->faker()->word,
                'first_name' => 'First',
                'last_name'  => 'Last',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('My Profile', $response);
        $this->assertResponseBodyContains('Invalid email address format', $response);
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id'         => $speaker->id,
                'email'      => $this->faker()->email,
                'first_name' => 'First',
                'last_name'  => 'Last',
            ]);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function displayChangePasswordWhenAllowed()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/profile/change_password');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Change Your Password', $response);
    }
}
