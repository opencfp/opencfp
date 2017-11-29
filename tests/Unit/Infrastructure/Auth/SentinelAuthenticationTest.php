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

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface as SentinelUserInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Infrastructure\Auth\SentinelAuthentication;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAuthentication
 */
class SentinelAuthenticationTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(SentinelAuthentication::class);
        $this->assertTrue($reflection->isFinal());
    }

    public function testIsInstanceOfAuthentication()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class)->makePartial();
        $auth     = new SentinelAuthentication($sentinel, $account);
        $this->assertInstanceOf(Authentication::class, $auth);
    }

    public function testAuthenticateWillThrowCorrectError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findByLogin')->andThrow(new UserNotFoundException());
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->expectException(AuthenticationException::class);
        $auth->authenticate('mail', 'pass');
    }

    public function tesAuthenticateWillThrowErrorWhenUnableToLogin()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('checkPassword')->andReturn(true);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findbyLogin')->andReturn($user);
        $sentinel->shouldReceive('login')->andReturn(false);
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->expectException(AuthenticationException::class);
        $auth->authenticate('mail', 'pass');
    }

    public function testAuthenticateWillThrowErrorWhenWrongPassword()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('checkPassword')->andReturn(false);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findbyLogin')->andReturn($user);
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->expectException(AuthenticationException::class);
        $auth->authenticate('mail', 'pass');
    }

    public function testAuthenticateIsVoidWhenSuccessFull()
    {
        $sentinelUser = Mockery::mock(SentinelUserInterface::class);
        $user         = Mockery::mock(UserInterface::class);
        $user->shouldReceive('checkPassword')->andReturn(true);
        $user->shouldReceive('getUser')->andReturn($sentinelUser);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('login')->andReturn(true);
        $account  = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findbyLogin')->andReturn($user);
        $auth = new SentinelAuthentication($sentinel, $account);
        $auth->authenticate('mail', 'pass');
    }

    public function testUserReturnsCorrectUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUser')->andReturn($user);
        $account = Mockery::mock(AccountManagement::class);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertInstanceOf(SentinelUser::class, $auth->user());
    }

    public function testUserThrowsCorrectErrorWhenNotFound()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUser')->andReturn(false);
        $account = Mockery::mock(AccountManagement::class);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->expectException(NotAuthenticatedException::class);
        $auth->user();
    }

    public function testUserIdReturnsId()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $user->shouldReceive('getUserId')->andReturn(3);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUser')->andReturn($user);
        $account = Mockery::mock(AccountManagement::class);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertSame(3, $auth->userId());
    }

    public function testCheckReturnsBool()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('check')->andReturn($user);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertTrue($auth->check());
    }

    public function testCheckReturnsFalseWhenNotLoggedIn()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertFalse($auth->check());
    }

    public function testGuestReturnsTheOppositeOfCheck()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('check')->andReturn($user);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertFalse($auth->guest());
    }

    public function testLogoutReturnsBool()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('logout')->andReturn($user);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertTrue($auth->logout());
    }
}
