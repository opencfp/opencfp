<?php

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Test\WebTestCase;

/**
 * @covers \OpenCFP\Http\Controller\PagesController
 */
class PagesControllerTest extends WebTestCase
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
            ->assertStatus(404)
            ->assertSee('Page Not Found!')
            ->assertNoFlashSet();
    }
}
