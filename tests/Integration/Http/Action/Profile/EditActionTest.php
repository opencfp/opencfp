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

namespace OpenCFP\Test\Integration\Http\Action\Profile;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\RequiresDatabaseReset;
use OpenCFP\Test\Integration\WebTestCase;

final class EditActionTest extends WebTestCase implements RequiresDatabaseReset
{
    /**
     * @test
     */
    public function notAbleToSeeEditPageOfOtherPersonsProfile()
    {
        $faker = $this->faker();

        $id      = $faker->unique()->numberBetween(1);
        $otherId = $faker->unique()->numberBetween(1);

        $response = $this
            ->asLoggedInSpeaker($id)
            ->get('/profile/edit/' . $otherId);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class, 1)->create()->first();

        $id = $user->id;

        $response = $this
            ->asLoggedInSpeaker($id)
            ->get('/profile/edit/' . $id);

        $this->assertResponseIsSuccessful($response);
    }
}
