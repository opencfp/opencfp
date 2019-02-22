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

namespace OpenCFP\Test\Unit\Http\Action\Forgot;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Forgot\UpdatePasswordAction;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\Form;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class UpdatePasswordActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function rendersFormIfFormIsNotSubmitted()
    {
        $content = $this->faker()->text;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $resetFormView = $this->prophesize(Form\FormView::class);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest(Argument::exact($request))
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(false);

        $resetForm
            ->createView()
            ->shouldBeCalled()
            ->willReturn($resetFormView);

        $twig = $this->prophesize(Twig_Environment::class);

        $twig
            ->render(
                Argument::exact('user/reset_password.twig'),
                Argument::exact([
                    'form' => $resetFormView->reveal(),
                ])
            )
            ->shouldBeCalled()
            ->willReturn($content);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $this->prophesize(Services\AccountManagement::class)->reveal(),
            $twig->reveal(),
            $this->prophesize(Routing\Generator\UrlGeneratorInterface::class)->reveal()
        );

        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }

    /**
     * @test
     */
    public function rendersFormIfFormIsSubmittedButNotValid()
    {
        $content = $this->faker()->text;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $resetFormView = $this->prophesize(Form\FormView::class);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest(Argument::exact($request))
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->isValid()
            ->shouldBeCalled()
            ->willReturn(false);

        $resetForm
            ->createView()
            ->shouldBeCalled()
            ->willReturn($resetFormView);

        $twig = $this->prophesize(Twig_Environment::class);

        $twig
            ->render(
                Argument::exact('user/reset_password.twig'),
                Argument::exact([
                    'form' => $resetFormView->reveal(),
                ])
            )
            ->shouldBeCalled()
            ->willReturn($content);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $this->prophesize(Services\AccountManagement::class)->reveal(),
            $twig->reveal(),
            $this->prophesize(Routing\Generator\UrlGeneratorInterface::class)->reveal()
        );

        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }

    /**
     * @dataProvider providerEmptyResetCode
     *
     * @param mixed $resetCode
     *
     * @test
     */
    public function throwsExceptionIfResetCodeIsEmpty($resetCode)
    {
        $faker = $this->faker();

        $userId   = $faker->numberBetween(1);
        $password = $faker->password();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest(Argument::exact($request))
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->isValid()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->getData()
            ->shouldBeCalled()
            ->willReturn([
                'user_id'    => $userId,
                'reset_code' => $resetCode,
                'password'   => $password,
            ]);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $this->prophesize(Services\AccountManagement::class)->reveal(),
            $this->prophesize(Twig_Environment::class)->reveal(),
            $this->prophesize(Routing\Generator\UrlGeneratorInterface::class)->reveal()
        );

        $this->expectException(\Exception::class);

        $action($request->reveal());
    }

    public function providerEmptyResetCode(): array
    {
        $values = [
            'array-empty'  => [],
            'bool-false'   => false,
            'int-zero'     => 0,
            'null'         => null,
            'string-empty' => '',
            'string-zero'  => '0',
        ];

        return \array_map(function ($value) {
            return [
                $value,
            ];
        }, $values);
    }

    /**
     * @test
     */
    public function redirectsToLoginIfNewPasswordMatchesOldPassword()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;
        $password  = $faker->password();

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'Please select a different password than your current one.',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest(Argument::exact($request))
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->isValid()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->getData()
            ->shouldBeCalled()
            ->willReturn([
                'user_id'    => $userId,
                'reset_code' => $resetCode,
                'password'   => $password,
            ]);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->checkPassword(Argument::exact($password))
            ->shouldBeCalled()
            ->willReturn(true);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById($userId)
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('login'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $accountManagement->reveal(),
            $this->prophesize(Twig_Environment::class)->reveal(),
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
    public function redirectsToHomepageIfAttemptToResetPasswordFailed()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;
        $password  = $faker->password();

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'Password reset failed, please contact the administrator.',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest($request)
            ->shouldBeCalled();
        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->isValid()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->getData()
            ->shouldBeCalled()
            ->willReturn([
                'user_id'    => $userId,
                'reset_code' => $resetCode,
                'password'   => $password,
            ]);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->checkPassword(Argument::exact($password))
            ->shouldBeCalled()
            ->willReturn(false);

        $user
            ->attemptResetPassword(
                Argument::exact($resetCode),
                Argument::exact($password)
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById($userId)
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('homepage'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $accountManagement->reveal(),
            $this->prophesize(Twig_Environment::class)->reveal(),
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
    public function redirectsToLoginIfAttemptToResetPasswordSucceeded()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;
        $password  = $faker->password();

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\Session::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'success',
                    'short' => 'Success',
                    'ext'   => "You've successfully reset your password.",
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest($request)
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->isValid()
            ->shouldBeCalled()
            ->willReturn(true);

        $resetForm
            ->getData()
            ->shouldBeCalled()
            ->willReturn([
                'user_id'    => $userId,
                'reset_code' => $resetCode,
                'password'   => $password,
            ]);

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->checkPassword(Argument::exact($password))
            ->shouldBeCalled()
            ->willReturn(false);

        $user
            ->attemptResetPassword(
                Argument::exact($resetCode),
                Argument::exact($password)
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById($userId)
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('login'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new UpdatePasswordAction(
            $resetForm->reveal(),
            $accountManagement->reveal(),
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
