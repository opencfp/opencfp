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

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Http\Action\Talk\EditAction;
use OpenCFP\Http\View;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class EditActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function redirectsToDashboardIfCallForPapersIsClosed()
    {
        $faker = $this->faker();

        $talkId = $faker->numberBetween(1);
        $url    = $faker->slug();

        $session = $this->createSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo('flash'),
                $this->identicalTo([
                    'type'  => 'error',
                    'short' => 'Read Only',
                    'ext'   => 'You cannot edit talks once the call for papers has ended',
                ])
            );

        $request = $this->createRequestMock();

        $request
            ->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo('id'))
            ->willReturn((string) $talkId);

        $request
            ->expects($this->at(1))
            ->method('getSession')
            ->willReturn($session);

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->never())
            ->method($this->anything());

        $talkHelper = $this->createTalkHelperMock();

        $talkHelper
            ->expects($this->never())
            ->method($this->anything());

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->identicalTo('talk_view'),
                $this->identicalTo([
                    'id' => $talkId,
                ])
            )
            ->willReturn($url);

        $action = new EditAction(
            $authentication,
            $talkHelper,
            $callForPapers,
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
    public function redirectsToDashboardIfCallForPapersIsOpenButTalkIdIsEmpty()
    {
        $talkId = 0;
        $url    = $this->faker()->slug();

        $session = $this->createSessionMock();

        $session
            ->expects($this->never())
            ->method($this->anything());

        $request = $this->createRequestMock();

        $request
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('id'))
            ->willReturn((string) $talkId);

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->never())
            ->method($this->anything());

        $talkHelper = $this->createTalkHelperMock();

        $talkHelper
            ->expects($this->never())
            ->method($this->anything());

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('dashboard'))
            ->willReturn($url);

        $action = new EditAction(
            $authentication,
            $talkHelper,
            $callForPapers,
            $urlGenerator
        );

        /** @var HttpFoundation\RedirectResponse $response */
        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($url, $response->getTargetUrl());
    }

    /**
     * @deprecated
     *
     * @return Framework\MockObject\MockObject|View\TalkHelper
     */
    private function createTalkHelperMock(): View\TalkHelper
    {
        return $this->createMock(View\TalkHelper::class);
    }

    /**
     * @deprecated
     *
     * @return CallForPapers|Framework\MockObject\MockObject
     */
    private function createCallForPapersMock(): CallForPapers
    {
        return $this->createMock(CallForPapers::class);
    }
}
