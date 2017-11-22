<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery as m;
use OpenCFP\Infrastructure\Auth\SentinelUser;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelUser
 */
class SentinelUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function weHaveTheRightUser()
    {
        $user = new SentinelUser(m::mock(\Cartalyst\Sentinel\Users\UserInterface::class));
        $this->assertInstanceOf(\OpenCFP\Infrastructure\Auth\UserInterface::class, $user);
    }

    /**
     * @test
     */
    public function getIdWorks()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserId')->andReturn(2);
        $sentinelUser = new SentinelUser($innerUser);
        $this->assertSame(2, $sentinelUser->getId());
    }

    /**
     * @test
     */
    public function getLoginWorks()
    {
        $innerUser = m::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $innerUser->shouldReceive('getUserLogin')->andReturn('test@example.com');
        $sentinelUser = new SentinelUser($innerUser);
        $this->assertSame('test@example.com', $sentinelUser->getLogin());
    }

    /**
     * @test
     */
    public function getUserWorks()
    {
        $user      = new SentinelUser(m::mock(\Cartalyst\Sentinel\Users\UserInterface::class));
        $innerUser = $user->getUser();
        $this->assertInstanceOf(\Cartalyst\Sentinel\Users\UserInterface::class, $innerUser);
    }
}
