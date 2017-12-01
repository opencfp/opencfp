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

namespace OpenCFP\Test\Integration\Infrastructure\Auth;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAuthentication;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\DataBaseInteraction;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAuthentication
 */
class SentinelAuthenticationTest extends BaseTestCase
{
    use DataBaseInteraction;
    /**
     * @var SentinelAuthentication
     */
    private $sut;

    public function setUp()
    {
        parent::setUp();
        $sentinel = (new Sentinel())->getSentinel();
        $accounts = new SentinelAccountManagement($sentinel);
        $accounts->create('test@example.com', 'secret');
        $accounts->activate('test@example.com');
        $this->sut = new SentinelAuthentication($sentinel, $accounts);
    }

    /**
     * @test
     */
    public function existing_user_can_authenticate()
    {
        $this->sut->authenticate('test@example.com', 'secret');
        $this->assertTrue($this->sut->check());

        $user = $this->sut->user();

        $this->assertSame('test@example.com', $user->getLogin());
    }

    /**
     * @test
     */
    public function wrongUserCanNotAuthenticate()
    {
        $this->expectException(AuthenticationException::class);
        $this->sut->authenticate('wrong@user.com', 'secret');
    }

    /**
     * @test
     */
    public function wrongPasswordCantAuthenticate()
    {
        $this->expectException(AuthenticationException::class);
        $this->sut->authenticate('test@example.com', 'Secret');
    }

    /**
     * @test
     */
    public function userIdWorks()
    {
        $this->sut->authenticate('test@example.com', 'secret');
        $this->assertSame(1, $this->sut->userId());
    }

    /**
     * @test
     */
    public function checkWorks()
    {
        $this->assertFalse($this->sut->check());
        $this->sut->authenticate('test@example.com', 'secret');
        $this->assertTrue($this->sut->check());
    }

    /**
     * @test
     */
    public function guestWorks()
    {
        $this->assertTrue($this->sut->guest());
        $this->sut->authenticate('test@example.com', 'secret');
        $this->assertFalse($this->sut->guest());
    }
}
