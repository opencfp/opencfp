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
use Mockery as m;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Auth\SentinelIdentityProvider;
use OpenCFP\Test\BaseTestCase;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelIdentityProvider
 */
final class SentinelIdentityProviderTest extends BaseTestCase
{
    public function testIsFinal()
    {
        $this->assertClassIsFinal(SentinelIdentityProvider::class);
    }

    public function testImplementsIdentityProvider()
    {
        $this->assertClassImplementsInterface(IdentityProvider::class, SentinelIdentityProvider::class);
    }

    public function testGetCurrentUserThrowsNotAuthenticatedExceptionWhenNotAuthenticated()
    {
        $sentinel = $this->getSentinel();

        $sentinel
            ->shouldReceive('getUser')
            ->once()
            ->andReturnNull();

        $speakerRepository = $this->getSpeakerRepositoryMock();

        $speakerRepository->shouldNotReceive(m::any());

        $provider = new SentinelIdentityProvider(
            $sentinel,
            $speakerRepository
        );

        $this->expectException(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $provider->getCurrentUser();
    }

    public function testGetCurrentUserReturnsUserWhenAuthenticated()
    {
        $id = $this->faker()->randomNumber();

        $sentinelUser = $this->getSentinelUserMock();

        $sentinelUser
            ->shouldReceive('getUserId')
            ->once()
            ->andReturn($id);

        $sentinel = $this->getSentinel();

        $sentinel
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($sentinelUser);

        $user = $this->getUserMock();

        $speakerRepository = $this->getSpeakerRepositoryMock();

        $speakerRepository
            ->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($user);

        $provider = new SentinelIdentityProvider(
            $sentinel,
            $speakerRepository
        );

        $this->assertSame($user, $provider->getCurrentUser());
    }

    //
    // Factory Methods
    //

    /**
     * @return m\MockInterface|Sentinel
     */
    private function getSentinel()
    {
        return m::mock(Sentinel::class);
    }

    /**
     * @return \Cartalyst\Sentinel\Users\UserInterface|m\MockInterface
     */
    private function getSentinelUserMock()
    {
        return m::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
    }

    /**
     * @return m\MockInterface|SpeakerRepository
     */
    private function getSpeakerRepositoryMock()
    {
        return m::mock(SpeakerRepository::class);
    }

    /**
     * @return m\MockInterface|User
     */
    private function getUserMock()
    {
        return m::mock(User::class);
    }
}
