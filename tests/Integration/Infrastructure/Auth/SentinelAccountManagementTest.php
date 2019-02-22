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

namespace OpenCFP\Test\Integration\Infrastructure\Auth;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use OpenCFP\Domain\Model\User;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class SentinelAccountManagementTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @var SentinelAccountManagement
     */
    private $sut;

    /**
     * @var \Cartalyst\Sentinel\Sentinel
     */
    private $sentinel;

    protected function setUp()
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
        $this->assertSame('Test Account', "{$user->getUser()->first_name} {$user->getUser()->last_name}");
    }

    /**
     * @test
     */
    public function creatingDuplicateUserThrowsError()
    {
        $this->sut->create('test@example.com', 'secret', [
            'first_name' => 'Test',
            'last_name'  => 'Account',
        ]);
        $this->expectException(UserExistsException::class);
        $this->sut->create('test@example.com', 'asdfasf', [
            'first_name' => 'Second',
            'last_name'  => 'Account',
        ]);
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

        $user = $this->sut->findByLogin('test@example.com');
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
        $this->assertSame('test@example.com', $users[0]['email']);
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

        $this->sut->activate('test@example.com');

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
