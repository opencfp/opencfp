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

namespace OpenCFP\Test\Unit\Http\Action\Profile;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Profile\EditAction;
use OpenCFP\Infrastructure\Auth;
use OpenCFP\PathInterface;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class EditActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToDashboardIfAuthenticatedUserRequestSomeoneElsesProfile()
    {
        $faker = $this->faker();

        $userId      = $faker->unique()->numberBetween(1);
        $otherUserId = $faker->unique()->numberBetween(1);

        $url = $faker->url;

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->getId()
            ->shouldBeCalled()
            ->willReturn($userId);

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::is('flash'),
                Argument::is([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => "You cannot edit someone else's profile",
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::is('id'))
            ->shouldBeCalled()
            ->willReturn((string) $otherUserId);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $authentication = $this->prophesize(Services\Authentication::class);

        $authentication
            ->user()
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::is('dashboard'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new EditAction(
            $authentication->reveal(),
            $this->prophesize(PathInterface::class)->reveal(),
            $this->prophesize(Twig_Environment::class)->reveal(),
            $urlGenerator->reveal()
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }
}
