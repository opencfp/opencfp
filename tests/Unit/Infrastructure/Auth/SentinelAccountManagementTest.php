<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAccountManagement
 */
class SentinelAccountManagementTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

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
}
