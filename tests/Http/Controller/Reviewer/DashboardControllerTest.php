<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    use DatabaseTransaction;

    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }

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

    /**
     * @test
     */
    public function indexActionWorkWhenThereAreTalks()
    {
        factory(Talk::class, 10)->create();
        $this->asReviewer()
            ->get('/reviewer/')
            ->assertSee('title="I want to see this talk"')
            ->assertSee('Recent Talks')
            ->assertSuccessful();
    }
}
