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

namespace OpenCFP\Test\Unit\Http\Action;

use Localheinz\Test\Util\Helper;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services;
use OpenCFP\Domain\Speaker;
use OpenCFP\Http\Action\DashboardAction;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

/**
 * @covers \OpenCFP\Http\Action\DashboardAction
 */
final class DashboardActionTest extends Framework\TestCase
{
    use Helper;

    public function testRedirectsToLoginIfUserIsNotAuthenticated()
    {
        $url = $this->faker()->slug();

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('findProfile')
            ->willThrowException(new Services\NotAuthenticatedException());

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->never())
            ->method($this->anything());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('login'))
            ->willReturn($url);

        $action = new DashboardAction(
            $speakers,
            $twig,
            $urlGenerator
        );

        $response = $action();

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function testRendersDashboardIfUserIsAuthenticated()
    {
        $content = $this->faker()->text();

        $speakerProfile = $this->createSpeakerProfileMock();

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('findProfile')
            ->willReturn($speakerProfile);

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->identicalTo('dashboard.twig'),
                $this->identicalTo([
                    'profile' => $speakerProfile,
                ])
            )
            ->willReturn($content);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->never())
            ->method($this->anything());

        $action = new DashboardAction(
            $speakers,
            $twig,
            $urlGenerator
        );

        $response = $action();

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
    }

    /**
     * @return Framework\MockObject\MockObject|Speakers
     */
    private function createSpeakersMock(): Speakers
    {
        return $this->createMock(Speakers::class);
    }

    /**
     * @return Framework\MockObject\MockObject|Speaker\SpeakerProfile
     */
    private function createSpeakerProfileMock(): Speaker\SpeakerProfile
    {
        return $this->createMock(Speaker\SpeakerProfile::class);
    }

    /**
     * @return Framework\MockObject\MockObject|Twig_Environment
     */
    private function createTwigMock(): Twig_Environment
    {
        return $this->createMock(Twig_Environment::class);
    }

    /**
     * @return Framework\MockObject\MockObject|Routing\Generator\UrlGeneratorInterface
     */
    private function createUrlGeneratorMock(): Routing\Generator\UrlGeneratorInterface
    {
        return $this->createMock(Routing\Generator\UrlGeneratorInterface::class);
    }
}
