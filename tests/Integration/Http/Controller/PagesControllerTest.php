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

use OpenCFP\Test\WebTestCase;
use Symfony\Component\HttpFoundation;

/**
 * @covers \OpenCFP\Http\Controller\PagesController
 */
final class PagesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function showHomePageWorks()
    {
        $this->get('/')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Call For Papers Now Open!');
    }

    /**
     * @test
     */
    public function showHomeWorksWhenCFPIsClosed()
    {
        $this->callForPapersIsClosed()
            ->get('/')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Call for Papers has ended!');
    }

    /**
     * @test
     */
    public function showSpeakerPackageWorks()
    {
        $this->get('/package')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Speaker Information');
    }

    /**
     * @test
     */
    public function showSpeakerPackageWorksWhenCFPIsClosed()
    {
        $this->callForPapersIsClosed()
            ->get('/package')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Speaker Information');
    }

    /**
     * @test
     */
    public function showTalkIdeasWorks()
    {
        $this->get('/ideas')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Talk Ideas');
    }

    /**
     * @test
     */
    public function showTalkIdeasWorksWhenCFPIsClosed()
    {
        $this->callForPapersIsClosed()
            ->get('/ideas')
            ->assertSuccessful()
            ->assertNoFlashSet()
            ->assertSee('Talk Ideas');
    }

    /**
     * @test
     */
    public function aBadUrlGivesAnErrorPage()
    {
        $this->get('/asdf/')
            ->assertStatus(HttpFoundation\Response::HTTP_NOT_FOUND)
            ->assertSee('Page Not Found!')
            ->assertNoFlashSet();
    }
}
