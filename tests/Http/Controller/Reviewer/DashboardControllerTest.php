<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use OpenCFP\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function indexActionWorksWithoutTalks()
    {
        $this->asReviewer()
            ->get('/reviewer/')
            ->assertNotSee('title="I want to see this talk"')
            ->assertSee('Recent Talks')
            ->assertSuccessful();
    }
}
