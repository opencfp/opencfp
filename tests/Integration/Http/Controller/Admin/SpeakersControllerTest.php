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

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use Mockery;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class SpeakersControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $users;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$users = factory(User::class, 5)->create();
    }

    /**
     * @test
     */
    public function indexActionWorksCorrectly()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/speakers');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$users->first()->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function viewActionDisplaysCorrectly()
    {
        $user = self::$users->first();

        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/' . $user->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($user->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function viewActionRedirectsOnNonUser()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/7679');

        $this->assertResponseBodyNotContains('Other Information', $response);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('Error', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function promoteActionFailsOnUserNotFound()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/7679/promote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_promote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('We were unable to promote the Admin. Please try again.', $this->container->get('session'));
    }

    /**
     * Bit of mocking so we don't depend on who is an admin or not.
     *
     * @test
     */
    public function promoteActionFailsIfUserIsAlreadyRole()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findById')->andReturn($user);
        $this->swap(AccountManagement::class, $accounts);
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_promote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('User already is in the Admin group.', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function promoteActionWorksCorrectly()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_promote')
            ->getValue();

        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_promote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('admin/speakers', $response);
        $this->assertSessionHasFlashMessage('success', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function promoteActionFailsOnBadToken()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', [
                'role'     => 'Admin',
                'token'    => \uniqid(),
                'token_id' => 'admin_speaker_promote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }

    /**
     * @test
     */
    public function demoteActionFailsIfUserNotFound()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin()
            ->get('/admin/speakers/7679/demote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('We were unable to remove the Admin. Please try again.', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function demoteActionFailsIfDemotingSelf()
    {
        $user      = self::$users->last();
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin($user->id)
            ->get('/admin/speakers/' . $user->id . '/demote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('Sorry, you cannot remove yourself as Admin.', $this->container->get('session'));
    }

    /**
     * A Bit of mocking here so we don't depend on what accounts are actually admin or not
     *
     * @test
     */
    public function demoteActionWorksCorrectly()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getLogin');

        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findById')->andReturn($user);
        $accounts->shouldReceive('demoteFrom');
        $this->swap(AccountManagement::class, $accounts);
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('admin_speaker_demote')
            ->getValue();

        $response = $this
            ->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/' . self::$users->last()->id . '/demote', [
                'role'     => 'Admin',
                'token'    => $csrfToken,
                'token_id' => 'admin_speaker_demote',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/admin/speakers', $response);
        $this->assertSessionHasFlashMessage('success', $this->container->get('session'));
    }

    /**
     * @test
     */
    public function demoteActionFailsWithBadToken()
    {
        $response = $this
            ->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/' . self::$users->last()->id . '/demote', [
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
        $response = $this
            ->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/delete/' . self::$users->last()->id . '?token_id=admin_speaker_demote&token=' . \uniqid());

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
