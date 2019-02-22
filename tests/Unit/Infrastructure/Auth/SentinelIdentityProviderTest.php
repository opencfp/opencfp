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
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Repository\UserRepository;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Infrastructure\Auth\SentinelIdentityProvider;
use PHPUnit\Framework;

final class SentinelIdentityProviderTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(SentinelIdentityProvider::class);
    }

    /**
     * @test
     */
    public function implementsIdentityProvider()
    {
        $this->assertClassImplementsInterface(IdentityProvider::class, SentinelIdentityProvider::class);
    }

    /**
     * @test
     */
    public function getCurrentUserThrowsNotAuthenticatedExceptionWhenNotAuthenticated()
    {
        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $userRepository = $this->createUserRepositoryMock();

        $userRepository
            ->expects($this->never())
            ->method($this->anything());

        $provider = new SentinelIdentityProvider(
            $sentinel,
            $userRepository
        );

        $this->expectException(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $provider->getCurrentUser();
    }

    /**
     * @test
     */
    public function getCurrentUserReturnsUserWhenAuthenticated()
    {
        $id = $this->faker()->randomNumber();

        $sentinelUser = $this->createSentinelUserMock();

        $sentinelUser
            ->expects($this->once())
            ->method('getUserId')
            ->willReturn($id);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($sentinelUser);

        $user = $this->createUserMock();

        $userRepository = $this->createUserRepositoryMock();

        $userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->identicalTo($id))
            ->willReturn($user);

        $provider = new SentinelIdentityProvider(
            $sentinel,
            $userRepository
        );

        $this->assertSame($user, $provider->getCurrentUser());
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|Sentinel
     */
    private function createSentinelMock(): Sentinel
    {
        return $this->createMock(Sentinel::class);
    }

    /**
     * @deprecated
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|Framework\MockObject\MockObject
     */
    private function createSentinelUserMock(): \Cartalyst\Sentinel\Users\UserInterface
    {
        return $this->createMock(\Cartalyst\Sentinel\Users\UserInterface::class);
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|UserRepository
     */
    private function createUserRepositoryMock(): UserRepository
    {
        return $this->createMock(UserRepository::class);
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|User
     */
    private function createUserMock(): User
    {
        return $this->createMock(User::class);
    }
}
