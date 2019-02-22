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

namespace OpenCFP\Test\Unit\Http\Action\Admin\Speaker;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Admin\Speaker\PromoteAction;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class PromoteActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToAdminSpeakersIfUserNotFound()
    {
        $faker = $this->faker();

        $role = $faker->word;
        $id   = $faker->numberBetween(1);

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => \sprintf(
                        'User with id "%s" could not be found.',
                        $id
                    ),
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('role'))
            ->shouldBeCalled()
            ->willReturn($role);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn((string) $id);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($id))
            ->shouldBeCalled()
            ->willThrow(new Auth\UserNotFoundException());

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('admin_speakers'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new PromoteAction(
            $accountManagement->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function redirectsToAdminSpeakersIfUserAlreadyBelongsToRole()
    {
        $faker = $this->faker();

        $role = $faker->word;
        $id   = $faker->numberBetween(1);

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => \sprintf(
                        'User already is in the "%s" group.',
                        $role
                    ),
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('role'))
            ->shouldBeCalled()
            ->willReturn($role);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn((string) $id);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->hasAccess(Argument::exact($role))
            ->shouldBeCalled()
            ->willReturn(true);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($id))
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('admin_speakers'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new PromoteAction(
            $accountManagement->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function redirectsToAdminSpeakersIfRoleNotFound()
    {
        $faker = $this->faker();

        $role  = $faker->word;
        $id    = $faker->numberBetween(1);
        $email = $faker->email;

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => \sprintf(
                        'Role "%s" could not be found.',
                        $role
                    ),
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('role'))
            ->shouldBeCalled()
            ->willReturn($role);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn((string) $id);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->hasAccess(Argument::exact($role))
            ->shouldBeCalled()
            ->willReturn(false);

        $user
            ->getLogin()
            ->shouldBeCalled()
            ->willReturn($email);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($id))
            ->shouldBeCalled()
            ->willReturn($user);

        $accountManagement
            ->promoteTo(
                Argument::exact($email),
                Argument::exact($role)
            )
            ->shouldBeCalled()
            ->willThrow(new Auth\RoleNotFoundException());

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate('admin_speakers')
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new PromoteAction(
            $accountManagement->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function redirectsToAdminSpeakersIfPromotingToAdminSucceeded()
    {
        $faker = $this->faker();

        $role  = $faker->word;
        $id    = $faker->numberBetween(1);
        $email = $faker->email;

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'success',
                    'short' => 'Success',
                    'ext'   => '',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('role'))
            ->shouldBeCalled()
            ->willReturn($role);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn((string) $id);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->hasAccess(Argument::exact($role))
            ->shouldBeCalled()
            ->willReturn(false);

        $user
            ->getLogin()
            ->shouldBeCalled()
            ->willReturn($email);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($id))
            ->shouldBeCalled()
            ->willReturn($user);

        $accountManagement
            ->promoteTo(
                Argument::exact($email),
                Argument::exact($role)
            )
            ->shouldBeCalled();

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate('admin_speakers')
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new PromoteAction(
            $accountManagement->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }
}
