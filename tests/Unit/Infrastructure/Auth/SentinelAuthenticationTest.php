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

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface as SentinelUserInterface;
use Localheinz\Test\Util\Helper;
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

final class SentinelAuthenticationTest extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(SentinelAuthentication::class);
    }

    /**
     * @test
     */
    public function isInstanceOfAuthentication()
    {
        $this->assertClassImplementsInterface(Authentication::class, SentinelAuthentication::class);
    }

    /**
     * @test
     */
    public function authenticateWillThrowCorrectErrorForMissingAccount()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findByLogin')->andThrow(new UserNotFoundException());
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->expectException(UserNotFoundException::class);
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

    /**
     * @test
     */
    public function authenticateWillThrowErrorWhenWrongPassword()
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

    /**
     * @test
     */
    public function authenticateIsVoidWhenSuccessFull()
    {
        $sentinelUser = Mockery::mock(SentinelUserInterface::class);
        $user         = Mockery::mock(UserInterface::class);
        $user->shouldReceive('checkPassword')->andReturn(true);
        $user->shouldReceive('getUser')->andReturn($sentinelUser);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('login')->andReturn(true);
        $account = Mockery::mock(AccountManagement::class);
        $account->shouldReceive('findByLogin')->andReturn($user);
        $auth = new SentinelAuthentication($sentinel, $account);
        $auth->authenticate('mail', 'pass');
    }

    /**
     * @test
     */
    public function userReturnsCorrectUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUser')->andReturn($user);
        $account = Mockery::mock(AccountManagement::class);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->assertInstanceOf(SentinelUser::class, $auth->user());
    }

    /**
     * @test
     */
    public function userThrowsCorrectErrorWhenNotFound()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUser')->andReturn(false);
        $account = Mockery::mock(AccountManagement::class);
        $auth    = new SentinelAuthentication($sentinel, $account);
        $this->expectException(NotAuthenticatedException::class);
        $auth->user();
    }

    /**
     * @test
     */
    public function checkReturnsBool()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('check')->andReturn($user);
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->assertTrue($auth->isAuthenticated());
    }

    /**
     * @test
     */
    public function checkReturnsFalseWhenNotLoggedIn()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->assertFalse($auth->isAuthenticated());
    }

    /**
     * @test
     */
    public function logoutReturnsBool()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $account  = Mockery::mock(AccountManagement::class);
        $sentinel->shouldReceive('logout')->andReturn($user);
        $auth = new SentinelAuthentication($sentinel, $account);
        $this->assertTrue($auth->logout());
    }
}
