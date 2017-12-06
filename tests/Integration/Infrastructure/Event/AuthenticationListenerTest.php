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
    public function testNoLoginRequired()
    {
        $response = $this->get('/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    public function testTalksRouteRequireLogin()
    {
        $response = $this->get('/talk/create');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testTalksRouteWithLogin()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/talk/create');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    public function testReviewerDashboardRequiresLogin()
    {
        $response = $this->get('/reviewer/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testReviewerDashboardRequiresReviewer()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/reviewer/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testReviewerDashboardAsReviewer()
    {
        $response = $this
            ->asReviewer()
            ->get('/reviewer/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    public function testAdminDashboardRequiresLogin()
    {
        $response = $this->get('/admin/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testAdminDashboardRequiresAdmin()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/admin/');

        $url = $this->container->get('url_generator')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testAdminDashboardAsAdmin()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }
}
