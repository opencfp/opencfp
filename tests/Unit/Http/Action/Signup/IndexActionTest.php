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

namespace OpenCFP\Test\Unit\Http\Action\Signup;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Http\Action\Signup\IndexAction;
use OpenCFP\Test\Unit\Http\Action\AbstractActionTestCase;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class IndexActionTest extends AbstractActionTestCase
{
    /**
     * @test
     */
    public function redirectsToDashboardIfSignedIn()
    {
        $url = $this->faker()->url;

        $request = $this->createRequestMock();

        $request
            ->expects($this->never())
            ->method($this->anything());

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->never())
            ->method($this->anything());

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

        $action = new IndexAction(
            $authentication,
            $callForPapers,
            $twig,
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
    public function redirectsToHomePageNotSignedInAndCallForPapersIsClosed()
    {
        $url = $this->faker()->url;

        $session = $this->createSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo('flash'),
                $this->identicalTo([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => 'Sorry, the call for papers has ended.',
                ])
            );

        $request = $this->createRequestMock();

        $request
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->never())
            ->method($this->anything());

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo('homepage'))
            ->willReturn($url);

        $action = new IndexAction(
            $authentication,
            $callForPapers,
            $twig,
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
    public function rendersSignupWhenNotSignedInAndCallForPapersIsOpen()
    {
        $content = $this->faker()->text;

        $request = $this->createRequestMock();

        $request
            ->expects($this->never())
            ->method($this->anything());

        $authentication = $this->createAuthenticationMock();

        $authentication
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $callForPapers = $this->createCallForPapersMock();

        $callForPapers
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $twig = $this->createTwigMock();

        $twig
            ->expects($this->once())
            ->method('render')
            ->with($this->identicalTo('security/signup.twig'))
            ->willReturn($content);

        $urlGenerator = $this->createUrlGeneratorMock();

        $urlGenerator
            ->expects($this->never())
            ->method($this->anything());

        $action = new IndexAction(
            $authentication,
            $callForPapers,
            $twig,
            $urlGenerator
        );

        $response = $action($request);

        $this->assertInstanceOf(HttpFoundation\Response::class, $response);
        $this->assertSame(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($content, $response->getContent());
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
