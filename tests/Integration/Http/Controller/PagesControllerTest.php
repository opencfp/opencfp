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

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

final class PagesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function indexActionWorks()
    {
        $response = $this->get('/');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Call For Papers Now Open!', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function indexActionWorksWhenCFPIsClosed()
    {
        $response = $this
            ->callForPapersIsClosed()
            ->get('/');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Call for Papers has ended!', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function speakerPackageActionWorks()
    {
        $response = $this->get('/package');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Speaker Information', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function speakerPackageActionWorksWhenCFPIsClosed()
    {
        $response = $this
            ->callForPapersIsClosed()
            ->get('/package');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Speaker Information', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function talkIdeasActionWorks()
    {
        $response = $this->get('/ideas');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Talk Ideas', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function talkIdeasActionWorksWhenCFPIsClosed()
    {
        $response = $this
            ->callForPapersIsClosed()
            ->get('/ideas');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Talk Ideas', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function aBadUrlGivesAnErrorPage()
    {
        $response = $this->get('/asdf/');

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_NOT_FOUND, $response);
        $this->assertResponseBodyContains('Page Not Found!', $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }
}
