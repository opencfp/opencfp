<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

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

        $url = $this->container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testTalksRouteWithLogin()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/talk/create');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    public function testReviewerDashboardRequiresLogin()
    {
        $response = $this->get('/reviewer/');

        $url = $this->container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testReviewerDashboardRequiresReviewer()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/reviewer/');

        $url = $this->container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testReviewerDashboardAsReviewer()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    public function testAdminDashboardRequiresLogin()
    {
        $response = $this->get('/admin/');

        $url = $this->container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testAdminDashboardRequiresAdmin()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/admin/');

        $url = $this->container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    public function testAdminDashboardAsAdmin()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }
}
