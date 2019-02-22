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

namespace OpenCFP\Test\Unit\Http\Action;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services;
use OpenCFP\Domain\Speaker;
use OpenCFP\Http\Action\DashboardAction;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class DashboardActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function redirectsToLoginIfUserIsNotAuthenticated()
    {
        $url = $this->faker()->slug();

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('findProfile')
            ->willThrowException(new Services\NotAuthenticatedException());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('login'))
            ->willReturn($url);

        $action = new DashboardAction(
            $speakers,
            $urlGenerator
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action();

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function rendersDashboardIfUserIsAuthenticated()
    {
        $speakerProfile = $this->createSpeakerProfileMock();

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('findProfile')
            ->willReturn($speakerProfile);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->never())
            ->method($this->anything());

        $action = new DashboardAction(
            $speakers,
            $urlGenerator
        );

        $expected = [
            'profile' => $speakerProfile,
        ];

        $this->assertSame($expected, $action());
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|Speakers
     */
    private function createSpeakersMock(): Speakers
    {
        return $this->createMock(Speakers::class);
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|Speaker\SpeakerProfile
     */
    private function createSpeakerProfileMock(): Speaker\SpeakerProfile
    {
        return $this->createMock(Speaker\SpeakerProfile::class);
    }
}
