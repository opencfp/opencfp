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

namespace OpenCFP\Test\Unit\Http\Action\Profile;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Profile\EditAction;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class EditActionTest extends Framework\TestCase
{
    use Helper;

    public function testRedirectsToDashboardIfAuthenticatedUserDoesNotEqualId()
    {
        $faker = $this->faker();

        $userId = $faker->unique()->numberBetween(1);
        $id     = $faker->unique()->numberBetween(1);
        $url    = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => "You cannot edit someone else's profile",
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

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
            ->getId()
            ->shouldBeCalled()
            ->willReturn($userId);

        $authentication = $this->prophesize(Services\Authentication::class);

        $authentication
            ->user()
            ->shouldBeCalled()
            ->willReturn($user);

        $downloadFromPath = $faker->slug;

        $twig = $this->prophesize(Twig_Environment::class);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('dashboard'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new EditAction(
            $authentication->reveal(),
            $downloadFromPath,
            $twig->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }
}
