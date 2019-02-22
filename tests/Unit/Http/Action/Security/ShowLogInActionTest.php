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

namespace OpenCFP\Test\Unit\Http\Action\Security;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Security\ShowLogInAction;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ShowLogInActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToDashboardIfUserIsLoggedIn(): void
    {
        $faker    = $this->faker();
        $email    = $faker->email;
        $password = $faker->password;

        $request = $this->prophesize(HttpFoundation\Request::class);
        $request
            ->get(Argument::exact('email'))
            ->shouldBeCalled()
            ->willReturn($email);
        $request
            ->get(Argument::exact('password'))
            ->shouldBeCalled()
            ->willReturn($password);

        $authentication = $this->prophesize(Services\Authentication::class);
        $authentication
            ->authenticate(
                Argument::exact($email),
                Argument::exact($password)
            )
            ->shouldBeCalled();

        $twig = $this->prophesize(Twig_Environment::class);

        $url_generator = $this->prophesize(Routing\Generator\UrlGenerator::class);
        $url_generator->generate(Argument::exact('dashboard'))->willReturn('/dashboard');

        $sso = 'on';

        $action = new ShowLogInAction(
            $authentication->reveal(),
            $twig->reveal(),
            $url_generator->reveal(),
            $sso
        );

        $response = $action($request->reveal());
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function loginPageShouldBeShownToNonAuthenticatedUsers(): void
    {
        $faker            = $this->faker();
        $email            = $faker->email;
        $password         = $faker->password;
        $exceptionMessage = $faker->sentence;

        $request = $this->prophesize(HttpFoundation\Request::class);
        $request
            ->get(Argument::exact('email'))
            ->shouldBeCalled()
            ->willReturn($email);
        $request
            ->get(Argument::exact('password'))
            ->shouldBeCalled()
            ->willReturn($password);

        $authentication = $this->prophesize(Services\Authentication::class);
        $authentication
            ->authenticate(
                Argument::exact($email),
                Argument::exact($password)
            )
            ->shouldBeCalled()
            ->willThrow(new Services\AuthenticationException($exceptionMessage));

        $sso = 'on';

        $twig = $this->prophesize(Twig_Environment::class);
        $twig
            ->render('security/login.twig', ['sso' => $sso])
            ->shouldBeCalled();

        $url_generator = $this->prophesize(Routing\Generator\UrlGenerator::class);

        $action = new ShowLogInAction(
            $authentication->reveal(),
            $twig->reveal(),
            $url_generator->reveal(),
            $sso
        );

        $response = $action($request->reveal());
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
    }
}
