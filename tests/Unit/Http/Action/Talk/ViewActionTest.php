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

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model;
use OpenCFP\Http\Action\Talk\ViewAction;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class ViewActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function redirectsToDashboardIfUserIsNotAuthorized()
    {
        $faker = $this->faker();

        $talkId = $faker->numberBetween(1);
        $url    = $faker->slug();

        $request = $this->createRequestMock();

        $request
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('id'))
            ->willReturn((string) $talkId);

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('getTalk')
            ->with($this->identicalTo($talkId))
            ->willThrowException(new NotAuthorizedException());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('dashboard'))
            ->willReturn($url);

        $action = new ViewAction(
            $speakers,
            $urlGenerator
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function rendersDashboardIfUserIsAuthenticated()
    {
        $faker = $this->faker();

        $talkId = $faker->numberBetween(1);

        $request = $this->createRequestMock();

        $request
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('id'))
            ->willReturn((string) $talkId);

        $talk = $this->createTalkMock();

        $speakers = $this->createSpeakersMock();

        $speakers
            ->expects($this->once())
            ->method('getTalk')
            ->with($this->identicalTo($talkId))
            ->willReturn($talk);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->never())
            ->method($this->anything());

        $action = new ViewAction(
            $speakers,
            $urlGenerator
        );

        $expected = [
            'talkId' => $talkId,
            'talk'   => $talk,
        ];

        $this->assertSame($expected, $action($request));
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
     * @return Framework\MockObject\MockObject|Model\Talk
     */
    private function createTalkMock(): Model\Talk
    {
        return $this->createMock(Model\Talk::class);
    }
}
