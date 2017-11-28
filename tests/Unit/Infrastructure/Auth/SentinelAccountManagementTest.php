<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAccountManagement
 */
class SentinelAccountManagementTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;
    use MockeryPHPUnitIntegration;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(SentinelAccountManagement::class);
        $this->assertTrue($reflection->isFinal());
    }

    public function testInstanceOfAccountManagement()
    {
        $sentinel = (new \Cartalyst\Sentinel\Native\Facades\Sentinel())->getSentinel();
        $account  = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(AccountManagement::class, $account);
    }

    public function testFindByIdThrowsCorrectError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findById')->andReturn(null);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserNotFoundException::class);
        $account->findById(3);
    }

    public function testFindByIdReturnsSentinelUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findById')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->findById(3));
    }

    public function testFindByLoginThrowsCorrectError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserNotFoundException::class);
        $account->findByLogin('mail@mail.mail');
    }

    public function testFindByLoginReturnsSentinelUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->findByLogin('mail@mail.mail'));
    }

    public function testFindByRoleReturnsArray()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getRoleRepository->findByName->getUsers->toArray')->andReturn([]);
        $accounts = new SentinelAccountManagement($sentinel);
        $this->assertEquals([], $accounts->findByRole('blabla'));
    }

    public function testCreateThrowsCorrectErrorWhenUserAlreadyExists()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserExistsException::class);
        $account->create('mail@mail.mail', 'pass');
    }

    public function testCreateReturnsCorrectUserWhenCreatingOne()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $sentinel->shouldReceive('getUserRepository->create')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->create('mail@mail.mail', 'pass'));
    }

    public function testCreateDefaultsToThrowingError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $sentinel->shouldReceive('getUserRepository->create')->andReturn(false);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserExistsException::class);
        $account->create('mail@mail.mail', 'pass');
    }

    public function testActivateReturnsBool()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('getActivationRepository->create->getCode');
        $sentinel->shouldReceive('getActivationRepository->complete')->andReturn(true);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertTrue($account->activate('mail@mail'));
    }

    public function testPromoteToIsVoid()
    {
        $role = $this->getFaker()->word;

        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(2);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('getRoleRepository->findByName->users->attach')->andReturn(true);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertNull($account->promoteTo('mail@mail.mail', $role));
    }

    public function testDemoteFromIsVoid()
    {
        $role = $this->getFaker()->word;

        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(2);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('getRoleRepository->findByName->users->detach')->andReturn(true);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertNull($account->demoteFrom('mail@mail.mail', $role));
    }
}
