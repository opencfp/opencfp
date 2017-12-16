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
use Localheinz\Test\Util\Helper;
use Mockery;
use OpenCFP\Infrastructure\Auth\SentinelUser;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelUser
 */
final class SentinelUserTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    public function testIsFinal()
    {
        $this->assertClassIsFinal(SentinelUser::class);
    }

    public function testWeHaveTheRightUser()
    {
        $this->assertClassImplementsInterface(\OpenCFP\Infrastructure\Auth\UserInterface::class, SentinelUser::class);
    }

    public function testGetIdWorks()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserId')->andReturn(2);
        $sentinelUser = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertSame(2, $sentinelUser->getId());
    }

    public function testGetLoginWorks()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserLogin')->andReturn('test@example.com');
        $sentinelUser = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertSame('test@example.com', $sentinelUser->getLogin());
    }

    public function testGtUserWorks()
    {
        $user      = new SentinelUser(Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class), $this->getSentinel());
        $innerUser = $user->getUser();
        $this->assertInstanceOf(\Cartalyst\Sentinel\Users\UserInterface::class, $innerUser);
    }

    public function testHasAccessReturnsFalseWhenUserDoesNotHaveAccess()
    {
        $innerUser     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->id = 2;
        $innerUser->shouldReceive('contains')->andReturn('true');
        $user = new SentinelUser($innerUser, $this->getSentinel());
        $this->assertFalse($user->hasAccess('role'));
    }

    public function testHasAccessReturnsTrueIfWeHaveAccess()
    {
        $toReturn      = [(object) ['id' => 2], (object) ['id' => 3], (object) ['id' => 4]];
        $innerUser     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->id = 3;
        $sentinel      = Mockery::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getRoleRepository->findByName->getUsers')
            ->andReturn(collect($toReturn));
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->hasAccess('role'));
    }

    public function testHasAccessReturnsFalseWhenAnErrorOcuurs()
    {
        $sentinelMock = Mockery::mock(Sentinel::class);
        $sentinelMock->shouldReceive('getRoleRepository')->andThrow(new \ErrorException());
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $user      = new SentinelUser($innerUser, $sentinelMock);
        $this->assertFalse($user->hasAccess('role'));
    }

    public function testCheckMasswrodReturnsTrueWhenItMatches()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel  = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->validateCredentials')->andReturn(false);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertFalse($user->checkPassword('hello'));
    }

    public function testCheckResetPasswordCodeReturnsABool()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = Mockery::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getReminderRepository->exists')
            ->andReturn($innerUser);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->checkResetPasswordCode('asdfasdf'));
    }

    public function testCheckResetPasswordCodeReturnsFalseWhenItIsFalse()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = Mockery::mock(Sentinel::class);
        $sentinel
            ->shouldReceive('getReminderRepository->exists')
            ->andReturn(false);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertFalse($user->checkResetPasswordCode('asdfasdf'));
    }

    public function testGetResetPasswordCodeReturnsCorrect()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getReminderRepository->create')->andReturn((object) ['code' => 'blabla']);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertSame('blabla', $user->getResetPasswordCode());
    }

    public function testAttemptResetPasswordReturnsCorrectBool()
    {
        $innerUser = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel  = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getReminderRepository->complete')->andReturn(true);
        $user = new SentinelUser($innerUser, $sentinel);
        $this->assertTrue($user->attemptResetPassword('asdf', 'passwoord'));
    }

    public function getSentinel(): Sentinel
    {
        return (new \Cartalyst\Sentinel\Native\Facades\Sentinel())->getSentinel();
    }
}
