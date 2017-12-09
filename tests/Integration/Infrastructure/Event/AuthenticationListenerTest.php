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

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use OpenCFP\Test\Integration\WebTestCase;

use Symfony\Component\HttpFoundation;

/**
 * @covers \OpenCFP\Infrastructure\Event\AuthenticationListener
 */
final class AuthenticationListenerTest extends WebTestCase
{
    /**
     * @test
     */
    public function noLoginRequired()
    {
        $response = $this->get('/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    /**
     * @test
     */
    public function talksRouteRequireLogin()
    {
        $response = $this->get('/talk/create');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function talksRouteWithLogin()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/talk/create');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardRequiresLogin()
    {
        $response = $this->get('/reviewer/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardRequiresReviewer()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/reviewer/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardAsReviewer()
    {
        $response = $this
            ->asReviewer()
            ->get('/reviewer/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    /**
     * @test
     */
    public function adminDashboardRequiresLogin()
    {
        $response = $this->get('/admin/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function adminDashboardRequiresAdmin()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/admin/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function adminDashboardAsAdmin()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }
}
