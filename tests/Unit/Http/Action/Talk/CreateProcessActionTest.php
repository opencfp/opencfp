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

namespace OpenCFP\Test\Unit\Http\Action\Talk;

use HTMLPurifier;
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Talk\CreateProcessAction;
use OpenCFP\Http\View;
use PHPUnit\Framework;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class CreateProcessActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToDashboardIfCallForPapersIsClosed()
    {
        $faker = $this->faker();

        $url = $faker->url;

        $applicationEmail   = $faker->email;
        $applicationTitle   = $faker->sentence;
        $applicationEndDate = $faker->dateTime()->format('Y-m-d H:i:s');

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'You cannot create talks once the call for papers has ended',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $callForPapers = $this->prophesize(CallForPapers::class);

        $callForPapers
            ->isOpen()
            ->shouldBeCalled()
            ->willReturn(false);

        $twig = $this->prophesize(\Twig_Environment::class);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(Argument::exact('dashboard'))
            ->shouldBeCalled()
            ->willReturn($url);

        $action = new CreateProcessAction(
            $this->prophesize(Services\Authentication::class)->reveal(),
            $this->prophesize(View\TalkHelper::class)->reveal(),
            $callForPapers->reveal(),
            $this->prophesize(HTMLPurifier::class)->reveal(),
            $this->prophesize(\Swift_Mailer::class)->reveal(),
            $applicationEmail,
            $applicationTitle,
            $applicationEndDate,
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
