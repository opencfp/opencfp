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

namespace OpenCFP\Test\Integration\Http\Action\Admin\Speaker;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class PromoteActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function promoteActionFailsOnUserNotFound()
    {
        $id = $this->faker()->numberBetween(500);

        /** @var Model\User $admin */
        $admin = factory(Model\User::class, 1)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get(
                \sprintf(
                    '/admin/speakers/%s/promote',
                    $id
                ),
                [
                    'role'     => 'Admin',
                    'token'    => $csrfToken,
                    'token_id' => 'admin_speaker_promote',
                ]
            );

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);

        $flashMessage = \sprintf(
            'User with id "%s" could not be found.',
            $id
        );

        $this->assertSessionHasFlashMessage($flashMessage, $this->session());
    }

    /**
     * @test
     */
    public function promoteActionFailsIfUserIsAlreadyRole()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class, 1)->create()->first();

        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class, 1)->create()->first();

        /** @var Services\AccountManagement $accountManagement */
        $accountManagement = $this->container->get(Services\AccountManagement::class);

        $accountManagement ->promoteTo(
            $speaker->email,
            'admin'
        );

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get(
                \sprintf(
                    '/admin/speakers/%s/promote',
                    $speaker->id
                ),
                [
                    'role'     => 'Admin',
                    'token'    => $csrfToken,
                    'token_id' => 'admin_speaker_promote',
                ]
            );

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('User already is in the "Admin" group.', $this->session());
    }

    /**
     * @test
     */
    public function promoteActionWorksCorrectly()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class, 1)->create()->first();

        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class, 1)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get(
                \sprintf(
                    '/admin/speakers/%s/promote',
                    $speaker->id
                ),
                [
                    'role'     => 'Admin',
                    'token'    => $csrfToken,
                    'token_id' => 'admin_speaker_promote',
                ]
            );

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('success', $this->session());
    }

    /**
     * @test
     */
    public function promoteActionFailsOnBadToken()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class, 1)->create()->first();

        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get(
                \sprintf(
                    '/admin/speakers/%s/promote',
                    $speaker->id
                ),
                [
                    'role'     => 'Admin',
                    'token'    => \uniqid(),
                    'token_id' => 'admin_speaker_promote',
                ]
            );

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
