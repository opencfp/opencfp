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
use OpenCFP\Http\Action\Forgot\ResetProcessAction;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\Form;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ResetProcessActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @dataProvider providerEmptyResetCode
     *
     * @param mixed $resetCode
     *
     * @test
     */
    public function throwsExceptionIfResetCodeIsEmpty($resetCode)
    {
        $userId = $this->faker()->numberBetween(1);

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

        $action = new ResetProcessAction(
            $this->prophesize(Form\FormInterface::class)->reveal(),
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
    public function rendersFormIfFormIsNotSubmitted()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;

        $content = $faker->text;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

        $resetFormView = $this->prophesize(Form\FormView::class);

        $userElement = $this->prophesize(Form\FormInterface::class);

        $userElement
            ->setData(Argument::exact((string) $userId))
            ->shouldBeCalled();

        $resetCodeElement = $this->prophesize(Form\FormInterface::class);

        $resetCodeElement
            ->setData(Argument::exact($resetCode))
            ->shouldBeCalled();

        $resetForm = $this->prophesize(Form\FormInterface::class);

        $resetForm
            ->handleRequest(Argument::exact($request))
            ->shouldBeCalled();

        $resetForm
            ->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(false);

        $resetForm
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn($userElement);

        $resetForm
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCodeElement);

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

        $action = new ResetProcessAction(
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
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;

        $content = $faker->text;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

        $resetFormView = $this->prophesize(Form\FormView::class);

        $userElement = $this->prophesize(Form\FormInterface::class);

        $userElement
            ->setData(Argument::exact((string) $userId))
            ->shouldBeCalled();

        $resetCodeElement = $this->prophesize(Form\FormInterface::class);

        $resetCodeElement
            ->setData(Argument::exact($resetCode))
            ->shouldBeCalled();

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
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn($userElement);

        $resetForm
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCodeElement);

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

        $action = new ResetProcessAction(
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
    public function redirectsToForgotPasswordIfUserWasNotFound()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'The reset you have requested appears to be invalid, please try again.',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

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

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($userId))
            ->shouldBeCalled()
            ->willThrow(new Auth\UserNotFoundException());

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('forgot_password'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new ResetProcessAction(
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
    public function redirectsToForgotPasswordIfResetCodeIsInvalid()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;

        $url = $faker->url;

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'The reset you have requested appears to be invalid, please try again.',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

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

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->checkResetPasswordCode(Argument::exact($resetCode))
            ->shouldBeCalled()
            ->willReturn(false);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($userId))
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('forgot_password'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new ResetProcessAction(
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
    public function redirectsToForgotPasswordIfResetCodeIsValid()
    {
        $faker = $this->faker();

        $userId    = $faker->numberBetween(1);
        $resetCode = $faker->sha256;

        $url = $faker->url;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('user_id'))
            ->shouldBeCalled()
            ->willReturn((string) $userId);

        $request
            ->get(Argument::exact('reset_code'))
            ->shouldBeCalled()
            ->willReturn($resetCode);

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

        $user = $this->prophesize(Auth\UserInterface::class);

        $user
            ->checkResetPasswordCode(Argument::exact($resetCode))
            ->shouldBeCalled()
            ->willReturn(true);

        $accountManagement = $this->prophesize(Services\AccountManagement::class);

        $accountManagement
            ->findById(Argument::exact($userId))
            ->shouldBeCalled()
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('forgot_password'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new ResetProcessAction(
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
