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

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class SpeakersControllerTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function indexActionWorksCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var Eloquent\Collection|User[] $speakers */
        $speakers = factory(User::class, 3)->create();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers');

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasNoFlashMessage($this->session());

        foreach ($speakers as $speaker) {
            $this->assertResponseBodyContains($speaker->first_name, $response);
        }
    }

    /**
     * @test
     */
    public function viewActionDisplaysCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = factory(User::class, 3)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers/' . $speaker->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($speaker->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->session());
    }

    /**
     * @test
     */
    public function viewActionRedirectsOnNonUser()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers/' . $this->faker()->numberBetween(500));

        $this->assertResponseBodyNotContains('Other Information', $response);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('Error', $this->session());
    }

    /**
     * @test
     */
    public function demoteActionFailsIfUserNotFound()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get(
                \sprintf(
                    '/admin/speakers/%s/demote',
                    $this->faker()->numberBetween(500)
                ),
                [
                    'role'     => 'Admin',
                    'token'    => $csrfToken,
                    'token_id' => 'admin_speaker_demote',
                ]
            );

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('We were unable to remove the Admin. Please try again.', $this->session());
    }

    /**
     * @test
     */
    public function demoteActionFailsIfDemotingSelf()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers/' . $admin->id . '/demote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('Sorry, you cannot remove yourself as Admin.', $this->session());
    }

    /**
     * A Bit of mocking here so we don't depend on what accounts are actually admin or not
     *
     * @test
     */
    public function demoteActionWorksCorrectly()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $this->container->get(AccountManagement::class)->promoteTo(
            $speaker->email,
            'admin'
        );

        $csrfToken = $this->container->get('security.csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers/' . $speaker->id . '/demote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('success', $this->session());
    }

    /**
     * @test
     */
    public function demoteActionFailsWithBadToken()
    {
        /** @var User $admin */
        $admin = factory(User::class, 1)->create()->first();

        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $this->container->get(AccountManagement::class)->promoteTo(
            $speaker->email,
            'admin'
        );

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/speakers/' . $speaker->id . '/demote', [
                'role'     => 'Admin',
                'token'    => \uniqid(),
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }

    /**
     * @test
     */
    public function deleteActionFailsWithBadToken()
    {
        /** @var User $user */
        $user = factory(User::class, 1)->create()->first();

        /** @var User $otherUser */
        $otherUser = factory(User::class, 1)->create()->first();

        $response = $this
            ->asAdmin($user->id)
            ->get('/admin/speakers/delete/' . $otherUser->id . '?token_id=admin_speaker_demote&token=' . \uniqid());

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
