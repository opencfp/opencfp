<?php

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use Mockery;
use OpenCFP\Domain\Model\User;
use OpenCFP\Infrastructure\Auth\Contracts\AccountManagement;
use OpenCFP\Infrastructure\Auth\Contracts\UserInterface;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * @group db
 * @coversNothing
 */
class SpeakersControllerTest extends WebTestCase
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
        $this->asAdmin()
            ->get('/admin/speakers')
            ->assertSee(self::$users->first()->first_name)
            ->assertNoFlashSet()
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function viewActionDisplaysCorrectly()
    {
        $user = self::$users->first();
        $this->asAdmin()
            ->get('/admin/speakers/' . $user->id)
            ->assertSee($user->first_name)
            ->assertNoFlashSet()
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function viewActionRedirectsOnNonUser()
    {
        $this->asAdmin()
            ->get('/admin/speakers/7679')
            ->assertNotSee('Other Information')
            ->assertRedirect()
            ->assertTargetURLContains('admin/speakers')
            ->assertFlashContains('Error');
    }

    /**
     * @test
     */
    public function promoteActionFailsOnUserNotFound()
    {
        $this->asAdmin()
            ->get('/admin/speakers/7679/promote', ['role' => 'Admin'])
            ->assertFlashContains('We were unable to promote the Admin. Please try again.')
            ->assertRedirect()
            ->assertTargetURLContains('admin/speakers');
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
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('admin_speaker_promote')
            ->getValue();
        $this->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', ['role' => 'Admin', 'token' => $csrfToken])
            ->assertFlashContains('User already is in the Admin group.')
            ->assertRedirect()
            ->assertTargetURLContains('admin/speakers');
    }

    /**
     * @test
     */
    public function promoteActionWorksCorrectly()
    {
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('admin_speaker_promote')
            ->getValue();
        $this->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', ['role' => 'Admin', 'token' => $csrfToken])
            ->assertFlashContains('success')
            ->assertRedirect()
            ->assertTargetURLContains('admin/speakers');
    }

    /**
     * @test
     */
    public function promoteActionFailsOnBadToken()
    {
        $this->asAdmin()
            ->get('/admin/speakers/' . self::$users->first()->id . '/promote', ['role' => 'Admin', 'token' => \uniqid()])
            ->assertRedirect()
            ->assertTargetURLContains('admin/speakers');
    }

    /**
     * @test
     */
    public function demoteActionFailsIfUserNotFound()
    {
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('admin_speaker_demote')
            ->getValue();
        $this->asAdmin()
            ->get('/admin/speakers/7679/demote', ['role' => 'Admin', 'token' => $csrfToken])
            ->assertFlashContains('We were unable to remove the Admin. Please try again.')
            ->assertRedirect()
            ->assertTargetURLContains('/admin/speakers');
    }

    /**
     * @test
     */
    public function demoteActionFailsIfDemotingSelf()
    {
        $user      = self::$users->last();
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('admin_speaker_demote')
            ->getValue();
        $this->asAdmin($user->id)
            ->get('/admin/speakers/' . $user->id . '/demote', ['role' => 'Admin', 'token' => $csrfToken])
            ->assertFlashContains('Sorry, you cannot remove yourself as Admin.')
            ->assertRedirect()
            ->assertTargetURLContains('/admin/speakers');
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
        $csrfToken = $this->app['csrf.token_manager']
            ->getToken('admin_speaker_demote')
            ->getValue();
        $this->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/' . self::$users->last()->id . '/demote', ['role' => 'Admin', 'token' => $csrfToken])
            ->assertFlashContains('success')
            ->assertRedirect()
            ->assertTargetURLContains('/admin/speakers');
    }

    /**
     * @test
     */
    public function demoteActionFailsWithBadToken()
    {
        $this->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/' . self::$users->last()->id . '/demote', ['role' => 'Admin', 'token' => \uniqid()])
            ->assertRedirect()
            ->assertTargetURLContains('/admin/speakers');
    }

    /**
     * @test
     */
    public function deleteActionFailsWithBadToken()
    {
        $this->asAdmin(self::$users->first()->id)
            ->get('/admin/speakers/delete/' . self::$users->last()->id . '?token=' . \uniqid())
            ->assertRedirect()
            ->assertTargetURLContains('/admin/speakers');
    }
}
