<?php

namespace OpenCFP\Test\Infrastructure\Auth;

use Cartalyst\Sentry\Facades\Native\Sentry;
use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Provider\SymfonySentrySession;
use OpenCFP\Test\DatabaseTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SentryAccountManagementTest extends DatabaseTestCase
{
    /**
     * @var SentryAccountManagement
     */
    private $sut;

    public function setUp()
    {
        parent::setUp();

        $this->sut = new SentryAccountManagement($this->getSentry());
    }

    /** @test */
    public function can_create_users_with_credentials()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account'
        ]);

        $user = $this->sut->findByLogin('test@example.com');

        $this->assertEquals('Test Account', "{$user->first_name} {$user->last_name}");
    }

    /** @test */
    public function users_are_speakers_by_default()
    {
        $user = $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account'
        ]);

        $group = $user->getGroups()[0];
        
        $this->assertEquals('Speakers', $group->getName());
    }

    /** @test */
    public function can_promote_and_demote_user()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name' => 'Account'
        ]);

        $this->assertCount(0, $this->sut->findByRole('Admin'));

        $this->sut->promote('test@example.com');
        $this->assertCount(1, $this->sut->findByRole('Admin'));

        $this->sut->demote('test@example.com');
        $this->assertCount(0, $this->sut->findByRole('Admin'));
    }

    public function getSentry()
    {
        $hasher = new \Cartalyst\Sentry\Hashing\NativeHasher;
        $userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
        $groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
        $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
        $session = new SymfonySentrySession(new Session(new MockFileSessionStorage()));
        $cookie = new \Cartalyst\Sentry\Cookies\NativeCookie([]);

        $sentry = new \Cartalyst\Sentry\Sentry(
            $userProvider,
            $groupProvider,
            $throttleProvider,
            $session,
            $cookie
        );

        Sentry::setupDatabaseResolver($this->phinxPdo);
        $throttleProvider->disable();

        return $sentry;
    }
}
