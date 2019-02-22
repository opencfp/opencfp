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
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAuthentication;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class SentinelAuthenticationTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @var SentinelAuthentication
     */
    private $sut;

    protected function setUp()
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
        $this->assertTrue($this->sut->isAuthenticated());

        $user = $this->sut->user();

        $this->assertSame('test@example.com', $user->getLogin());
    }

    /**
     * @test
     */
    public function missingUserIsNotFoundCantAuthenticate()
    {
        $this->expectException(UserNotFoundException::class);
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
    public function checkWorks()
    {
        $this->assertFalse($this->sut->isAuthenticated());
        $this->sut->authenticate('test@example.com', 'secret');
        $this->assertTrue($this->sut->isAuthenticated());
    }
}
