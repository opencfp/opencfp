<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\WebTestCase;

class SpeakerControllerTest extends WebTestCase
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
    public function indexActionWorksWithNoSpeakers()
    {
        $this->asReviewer()
            ->get('/reviewer/speakers')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexActionDisplaysSpeakers()
    {
        $speaker = factory(User::class, 10)->create()->first();
        $this->asReviewer()
            ->get('/reviewer/speakers')
            ->assertSee($speaker->first_name)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function viewActionRedirectsWhenUserDoesntExist()
    {
        $this->asReviewer()
            ->get('/reviewer/speakers/255')
            ->assertNotSee('Speaker Bio')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function viewActionShowsSpeaker()
    {
        $speaker = factory(User::class)->create()->first();

        $this->asReviewer()
            ->get('/reviewer/speakers/'.$speaker->id)
            ->assertSee($speaker->first_name)
            ->assertSee($speaker->bio)
            ->assertSuccessful();
    }
}
