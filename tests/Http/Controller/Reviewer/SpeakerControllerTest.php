<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

class SpeakerControllerTest extends WebTestCase
{
    use RefreshDatabase;

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
        $speaker = factory(User::class, 2)->create()->first();
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
