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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\RoleAccess;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @covers \OpenCFP\Infrastructure\Auth\RoleAccess
 */
final class RoleAccessTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;
    use MockeryPHPUnitIntegration;

    public function testReturnsRedirectResponseIfCheckFailed()
    {
        $role = $this->getFaker()->word;

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);

        $this->assertInstanceOf(RedirectResponse::class, RoleAccess::userHasAccess($auth, $role));
    }

    public function testReturnsRedirectResponseIfCheckSucceededButUserHasAccess()
    {
        $role = $this->getFaker()->word;

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with($role)->andReturn(false);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $this->assertInstanceOf(RedirectResponse::class, RoleAccess::userHasAccess($auth, $role));
    }

    public function testReturnsNothingIfCheckSucceededAndUserHasAccess()
    {
        $role = $this->getFaker()->word;

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with($role)->andReturn(true);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $this->assertNull(RoleAccess::userHasAccess($auth, $role));
    }
}
