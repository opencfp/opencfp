<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2020 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

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

        $url = self::$container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function talksRouteWithLogin()
    {
        // Make sure the CFP is open
        $now           = new \DateTime();
        $callForPapers = self::$container->get(CallForPapers::class);
        $method        = new \ReflectionMethod(CallForPapers::class, 'setEndDate');
        $method->setAccessible(true);
        $method->invoke($callForPapers, new \DateTimeImmutable($now->format('M. jS, Y')));
        self::$container->get('twig')->addGlobal('cfp_open', $callForPapers->isOpen());

        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/talk/create');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardRequiresLogin()
    {
        $response = $this->get('/reviewer/');

        $url = self::$container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardRequiresReviewer()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/reviewer/');

        $url = self::$container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function reviewerDashboardAsReviewer()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }

    /**
     * @test
     */
    public function adminDashboardRequiresLogin()
    {
        $response = $this->get('/admin/');

        $url = self::$container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function adminDashboardRequiresAdmin()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->get('/admin/');

        $url = self::$container->get('router')->generate('dashboard');

        $this->assertRedirectResponseUrlEquals($url, $response);
    }

    /**
     * @test
     */
    public function adminDashboardAsAdmin()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
    }
}
