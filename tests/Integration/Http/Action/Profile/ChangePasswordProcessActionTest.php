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

namespace OpenCFP\Test\Integration\Http\Action\Profile;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ChangePasswordProcessActionTest extends WebTestCase implements TransactionalTestCase
{
    public function testRedirectsToPasswordEditIfDataIsMissing()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->post('/profile/change_password');

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlEquals('/profile/change_password', $response);
        $this->assertSessionHasFlashMessage('Missing passwords', $this->session());
    }

    public function testRedirectsToPasswordEditIfPasswordAndPasswordConfirmationAreEmptyStrings()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->post('/profile/change_password', [
                'password'         => '',
                'password_confirm' => '',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlEquals('/profile/change_password', $response);
        $this->assertSessionHasFlashMessage('Missing passwords', $this->session());
    }

    public function testRedirectsToPasswordEditIfPasswordDoesNotEqualPasswordConfirmation()
    {
        $faker = $this->faker();

        $password             = $faker->unique()->password;
        $passwordConfirmation = $faker->unique()->password;

        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->post('/profile/change_password', [
                'password'         => $password,
                'password_confirm' => $passwordConfirmation,
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlEquals('/profile/change_password', $response);
        $this->assertSessionHasFlashMessage('The submitted passwords do not match', $this->session());
    }
}
