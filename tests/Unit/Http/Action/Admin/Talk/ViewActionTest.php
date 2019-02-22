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

namespace OpenCFP\Test\Unit\Http\Action\Admin\Talk;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Talk;
use OpenCFP\Http\Action\Admin\Talk\ViewAction;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ViewActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToDashboardIfTalkCannotBeViewed()
    {
        $faker = $this->faker();

        $id  = $faker->numberBetween(1);
        $url = $faker->text;

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'Could not find requested talk',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn($id);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $talkHandler = $this->prophesize(Talk\TalkHandler::class);

        $talkHandler
            ->grabTalk(Argument::exact($id))
            ->shouldBeCalled();

        $talkHandler
            ->view()
            ->shouldBeCalled()
            ->willReturn(false);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('admin_talks'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new ViewAction(
            $talkHandler->reveal(),
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
    public function rendersTalkIfTalkCanBeViewed()
    {
        $faker = $this->faker();

        $id      = $faker->numberBetween(1);
        $content = $faker->text;

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn($id);

        $talkProfile = $this->prophesize(Talk\TalkProfile::class);

        $talkHandler = $this->prophesize(Talk\TalkHandler::class);

        $talkHandler
            ->grabTalk(Argument::exact($id))
            ->shouldBeCalled();

        $talkHandler
            ->view()
            ->shouldBeCalled()
            ->willReturn(true);

        $talkHandler
            ->getProfile()
            ->shouldBeCalled()
            ->willReturn($talkProfile);

        $twig = $this->prophesize(Twig_Environment::class);

        $twig
            ->render(
                Argument::exact('admin/talks/view.twig'),
                Argument::exact([
                    'talk' => $talkProfile->reveal(),
                ])
            )
            ->shouldBeCalled()
            ->willReturn($content);

        $action = new ViewAction(
            $talkHandler->reveal(),
            $twig->reveal(),
            $this->prophesize(Routing\Generator\UrlGeneratorInterface::class)->reveal()
        );

        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }
}
