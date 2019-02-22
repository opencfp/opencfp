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
use OpenCFP\Http\Action\Talk\UpdateAction;
use OpenCFP\Http\View;
use PHPUnit\Framework;
use Prophecy\Argument;
use Swift_Mailer;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class UpdateActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToDashboardIfCallForPapersIsClosed()
    {
        $faker = $this->faker();

        $talkId = $faker->numberBetween(1);
        $url    = $faker->slug();

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);

        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Read Only',
                    'ext'   => 'You cannot edit talks once the call for papers has ended',
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);

        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $request
            ->get(Argument::exact('id'))
            ->shouldBeCalled()
            ->willReturn($talkId);

        $callForPapers = $this->prophesize(CallForPapers::class);

        $callForPapers
            ->isOpen()
            ->shouldBeCalled()
            ->willReturn(false);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);

        $urlGenerator
            ->generate(
                Argument::exact('talk_view'),
                Argument::exact([
                    'id' => $talkId,
                ])
            )
            ->shouldBeCalled()
            ->willReturn($url);

        $applicationEmail   = $faker->email;
        $applicationTitle   = $faker->sentence;
        $applicationEndDate = $faker->dateTime()->format('Y-m-d H:i:s');

        $action = new UpdateAction(
            $this->prophesize(Services\Authentication::class)->reveal(),
            $this->prophesize(View\TalkHelper::class)->reveal(),
            $callForPapers->reveal(),
            $this->prophesize(HTMLPurifier::class)->reveal(),
            $this->prophesize(Swift_Mailer::class)->reveal(),
            $this->prophesize(\Twig_Environment::class)->reveal(),
            $urlGenerator->reveal(),
            $applicationEmail,
            $applicationTitle,
            $applicationEndDate
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request->reveal());

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }
}
