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

namespace OpenCFP\Test\Unit\Http\Action\Security;

use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Security\SsoAction;
use PHPUnit\Framework;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class SsoActionTest extends Framework\TestCase
{
    /** @var ObjectProphecy */
    private $urlGenerator;

    /** @var ObjectProphecy */
    private $request;

    private $clientId;

    private $redirectUri;

    private $authorizeUrl;

    public function setUp()
    {
        parent::setUp();
        $this->urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);
        $this->request      = $this->prophesize(HttpFoundation\Request::class);
        $this->clientId     = 'client-id';
        $this->redirectUri  = '/redirect';
        $this->authorizeUrl = '/authorize/';
    }

    /**
     * @test
     */
    public function redirectToDashboardIfAuthenticated(): void
    {
        $this->urlGenerator
            ->generate('dashboard')
            ->willReturn('/dashboard');

        $centralAction = new SsoAction(
            $this->createAuthenticationDouble(true),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->redirectUri,
            $this->authorizeUrl
        );
        $response = $centralAction($this->request->reveal());
        $needle   = 'Redirecting to /dashboard';
        $this->assertContains($needle, $response->getContent());
    }

    /**
     * @test
     */
    public function redirectToCentralIfNotAuthenticated(): void
    {
        $centralAction = new SsoAction(
            $this->createAuthenticationDouble(false),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->redirectUri,
            $this->authorizeUrl
        );
        $response = $centralAction($this->request->reveal());

        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
        $needle = "Redirecting to {$this->authorizeUrl}client_id={$this->clientId}&amp;redirect_uri=%2Fredirect";
        $this->assertContains($needle, $response->getContent());
    }

    private function createAuthenticationDouble(bool $isAuthenticated)
    {
        $authentication = $this->prophesize(Services\Authentication::class);
        $authentication
            ->isAuthenticated()
            ->willReturn($isAuthenticated);

        return $authentication->reveal();
    }
}
