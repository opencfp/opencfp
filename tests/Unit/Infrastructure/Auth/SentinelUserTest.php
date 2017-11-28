<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Mockery as m;
use OpenCFP\Infrastructure\Auth\SentinelUser;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelUser
 */
class SentinelUserTest extends \PHPUnit\Framework\TestCase
{
    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(SentinelUser::class);
        $this->assertTrue($reflection->isFinal());
    }

    public function testWeHaveTheRightUser()
    {
        $user = new SentinelUser(m::mock(\Cartalyst\Sentinel\Users\UserInterface::class), $this->getSentinel());
        $this->assertInstanceOf(\OpenCFP\Infrastructure\Auth\Contracts\UserInterface::class, $user);
    }

    public function testGetIdWorks()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserId')->andReturn(2);
        $sentinelUser = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertSame(2, $sentinelUser->getId());
    }

    public function testGetLoginWorks()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserLogin')->andReturn('test@example.com');
        $sentinelUser = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertSame('test@example.com', $sentinelUser->getLogin());
    }

    public function testGtUserWorks()
    {
        $user      = new SentinelUser(m::mock(\Cartalyst\Sentinel\Users\UserInterface::class), $this->getSentinel());
        $innerUser = $user->getUser();
        $this->assertInstanceOf(\Cartalyst\Sentinel\Users\UserInterface::class, $innerUser);
    }

    public function testHasAccessReturnsFalseWhenUserDoesNotHaveAccess()
    {
        $innerUser     = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->id = 2;
        $innerUser->shouldReceive('contains')->andReturn('true');
        $user = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertFalse($user->hasAccess('role'));
    }

    public function testHasAccessReturnsTrueIfWeHaveAccess()
    {
        $toReturn      = [(object) ['id' => 2], (object) ['id' => 3], (object) ['id' => 4]];
        $innerUser     = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->id = 3;
        $sentinel      = m::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getRoleRepository->findByName->getUsers')
            ->andReturn(collect($toReturn));
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->hasAccess('role'));
    }

    public function testHasAccessReturnsFalseWhenAnErrorOcuurs()
    {
        $sentinelMock = m::mock(Sentinel::class);
        $sentinelMock->shouldReceive('getRoleRepository')->andThrow(new \ErrorException());
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $user      = new SentinelUser($innerUser, $sentinelMock);
        $this->assertFalse($user->hasAccess('role'));
    }

    public function testCheckMasswrodReturnsTrueWhenItMatches()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel  = m::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->validateCredentials')->andReturn(false);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertFalse($user->checkPassword('hello'));
    }

    public function testCheckResetPasswordCodeReturnsABool()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = m::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getReminderRepository->exists')
            ->andReturn($innerUser);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->checkResetPasswordCode('asdfasdf'));
    }

    public function testCheckResetPasswordCodeReturnsFalseWhenItIsFalse()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = m::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getReminderRepository->exists')
            ->andReturn(false);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertFalse($user->checkResetPasswordCode('asdfasdf'));
    }

    public function testGetResetPasswordCodeReturnsCorrect()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = m::mock(Sentinel::class);
        $sentinel->shouldReceive('getReminderRepository->create')->andReturn((object) ['code' => 'blabla']);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertSame('blabla', $user->getResetPasswordCode());
    }

    public function testAttemptResetPasswordReturnsCorrectBool()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = m::mock(Sentinel::class);
        $sentinel->shouldReceive('getReminderRepository->complete')->andReturn(true);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->attemptResetPassword('asdf', 'passwoord'));
    }

    public function getSentinel(): Sentinel
    {
        return (new \Cartalyst\Sentinel\Native\Facades\Sentinel())->getSentinel();
    }
}
