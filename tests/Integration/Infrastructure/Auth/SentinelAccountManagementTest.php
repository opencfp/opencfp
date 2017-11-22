<?php

namespace OpenCFP\Test\Integration\Infrastructure\Auth;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentry\Users\Eloquent\User;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\DataBaseInteraction;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAccountManagement
 */
class SentinelAccountManagementTest extends BaseTestCase
{
    use DataBaseInteraction;

    /**
     * @var SentinelAccountManagement
     */
    private $sut;

    /**
     * @var \Cartalyst\Sentinel\Sentinel
     */
    private $sentinel;

    public function setUp()
    {
        parent::setUp();
        $this->sentinel = (new Sentinel())->getSentinel();
        $this->sut      = new SentinelAccountManagement($this->sentinel);
    }

    /**
     * @test
     */
    public function canCreateUserWithCredentials()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);

        $user = $this->sut->findByLogin('test@example.com');
        $this->assertEquals('Test Account', "{$user->getUser()->first_name} {$user->getUser()->last_name}");
    }

    /**
     * @test
     */
    public function findByIdWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $someUser = User::first();

        $sentinelUser = $this->sut->findById($someUser->id);
        $this->assertSame($sentinelUser->getLogin(), $someUser->email);
    }

    /**
     * @test
     */
    public function findByLoginWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);

        $user =$this->sut->findByLogin('test@example.com');
        $this->assertSame('test@example.com', $user->getLogin());
    }

    /**
     * @test
     */
    public function findByRoleWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $this->sut->promoteTo('test@example.com', 'Admin');
        $users = $this->sut->findByRole('Admin');
        $this->assertCount(1, $users);
        $this->assertSame('test@example.com', $users->first()->email);
    }

    /**
     * @test
     */
    public function activateWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $user = $this->sut->findByLogin('test@example.com')->getUser();
        //Check there are no records of activation for the user;
        $this->assertFalse($this->sentinel->getActivationRepository()->exists($user));
        $this->assertTrue($this->sut->activate('test@example.com'));
        //Check we completed activation
        $this->assertTrue($this->sentinel->getActivationRepository()->completed($user)->completed);
    }

    /**
     * @test
     */
    public function promoteToWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $this->sut->promoteTo('test@example.com', 'Admin');
        $user = $this->sut->findByLogin('test@example.com');
        $this->assertTrue($user->hasAccess('Admin'));
    }

    /**
     * @test
     */
    public function demoteFromWorks()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $this->sut->promoteTo('test@example.com', 'Admin');
        $user = $this->sut->findByLogin('test@example.com');
        $this->assertTrue($user->hasAccess('Admin'));
        $this->sut->demoteFrom('test@example.com', 'Admin');
        $this->assertFalse($user->hasAccess('Admin'));
    }
}
