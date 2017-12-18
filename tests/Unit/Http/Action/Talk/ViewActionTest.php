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
    public function testRedirectsToDashboardIfUserIsNotAuthorized()
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

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->never())
            ->method($this->anything());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('dashboard'))
            ->willReturn($url);

        $action = new ViewAction(
            $speakers,
            $twig,
            $urlGenerator
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testRendersDashboardIfUserIsAuthenticated()
    {
        $faker = $this->faker();

        $talkId  = $faker->numberBetween(1);
        $content = $faker->text();

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

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->identicalTo('talk/view.twig'),
                $this->identicalTo([
                    'talkId' => $talkId,
                    'talk'   => $talk,
                ])
            )
            ->willReturn($content);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->never())
            ->method($this->anything());

        $action = new ViewAction(
            $speakers,
            $twig,
            $urlGenerator
        );

        $response = $action($request);

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
     * @return Framework\MockObject\MockObject|Model\Talk
     */
    private function createTalkMock(): Model\Talk
    {
        return $this->createMock(Model\Talk::class);
    }
}
