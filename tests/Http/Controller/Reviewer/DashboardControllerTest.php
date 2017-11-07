<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use OpenCFP\Test\TestCase;

class DashboardControllerTest extends TestCase
{
    /**
     * @test
     */
    public function indexActionWorksWithoutTalks()
    {
        $this->asReviewer()
            ->get('/reviewer/')
            ->assertSuccessful()
            ->assertNotSee('title="I want to see this talk"')
            ->assertSee('Recent Talks');
    }
}
