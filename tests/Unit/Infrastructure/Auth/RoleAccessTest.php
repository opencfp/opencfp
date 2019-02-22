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

use Localheinz\Test\Util\Helper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\RoleAccess;
use OpenCFP\Infrastructure\Auth\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class RoleAccessTest extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function returnsRedirectResponseIfCheckFailed()
    {
        $role = $this->faker()->word;

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(false);

        $this->assertInstanceOf(RedirectResponse::class, RoleAccess::userHasAccess($auth, $role));
    }

    /**
     * @test
     */
    public function returnsRedirectResponseIfCheckSucceededButUserHasAccess()
    {
        $role = $this->faker()->word;

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with($role)->andReturn(false);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $this->assertInstanceOf(RedirectResponse::class, RoleAccess::userHasAccess($auth, $role));
    }

    /**
     * @test
     */
    public function returnsNothingIfCheckSucceededAndUserHasAccess()
    {
        $role = $this->faker()->word;

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with($role)->andReturn(true);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $this->assertNull(RoleAccess::userHasAccess($auth, $role));
    }
}
