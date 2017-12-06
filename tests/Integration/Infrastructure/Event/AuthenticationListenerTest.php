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

/**
 * @covers \OpenCFP\Infrastructure\Event\AuthenticationListener
 */
final class AuthenticationListenerTest extends WebTestCase
{
    public function testNoLoginRequired()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testTalksRouteRequireLogin()
    {
        $response = $this->get('/talk/create');

        $response->assertRedirect('dashboard');
    }

    public function testTalksRouteWithLogin()
    {
        $response = $this->asLoggedInSpeaker()->get('/talk/create');

        $response->assertStatus(200);
    }

    public function testReviewerDashboardRequiresLogin()
    {
        $response = $this->get('/reviewer/');

        $response->assertRedirect('dashboard');
    }

    public function testReviewerDashboardRequiresReviewer()
    {
        $response = $this->asLoggedInSpeaker()->get('/reviewer/');

        $response->assertRedirect('dashboard');
    }

    public function testReviewerDashboardAsReviewer()
    {
        $response = $this->asReviewer()->get('/reviewer/');

        $response->assertStatus(200);
    }

    public function testAdminDashboardRequiresLogin()
    {
        $response = $this->get('/admin/');

        $response->assertRedirect('dashboard');
    }

    public function testAdminDashboardRequiresAdmin()
    {
        $response = $this->asLoggedInSpeaker()->get('/admin/');

        $response->assertRedirect('dashboard');
    }

    public function testAdminDashboardAsAdmin()
    {
        $response = $this->asAdmin()->get('/admin/');

        $response->assertStatus(200);
    }
}
