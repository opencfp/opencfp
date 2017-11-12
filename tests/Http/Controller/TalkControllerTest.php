<?php

namespace OpenCFP\Test\Http\Controller;

use Mockery as m;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * Class TalkControllerTest
 *
 * @package OpenCFP\Test\Http\Controller
 * @group db
 */
class TalkControllerTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * Verify that talks with ampersands and other characters in them can
     * be created and then edited properly
     *
     * @test
     */
    public function ampersandsAcceptableCharacterForTalks()
    {
        // Create a test double for SwiftMailer
        $swiftMailer = m::mock(\stdClass::class);
        $swiftMailer->shouldReceive('send')->andReturn(true);
        $this->swap('mailer', $swiftMailer);

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Title With Ampersand',
            'description' => 'The title should contain this & that',
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'user_id' => 1,
        ];

        $this->asLoggedInSpeaker(1)
            ->callForPapersIsOpen()
            ->post('/talk/create', $talk_data)
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function allowSubmissionsUntilRightBeforeMidnightDayOfClose()
    {
        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new \DateTime();
        $this->swap('callforproposal', new CallForProposal(new \DateTime($now->format('M. jS, Y'))));

        /*
         * This should not have a flash message. The fact that this
         * is true means code is working as intended. Previously this fails
         * because the CFP incorrectly ended at 12:00am the day of, not 11:59pm.
         */
        $controller->createAction($this->req);

        $flashMessage = $this->app['session']->get('flash');
        $this->assertNull($flashMessage);

        /*
         * However, if I update application configuration to make
         * the CFP end date to be "yesterday" then we get flash as expected.
         */
        $yesterday = new \DateTime('yesterday');

        $this->app['callforproposal'] = new CallForProposal(new \DateTime($yesterday->format('M. jS, Y')));

        $controller->createAction($this->req);

        $flashMessage = $this->app['session']->get('flash');
        $this->assertEquals('error', $flashMessage['type']);
        $this->assertEquals('You cannot create talks once the call for papers has ended', $flashMessage['ext']);
    }

    /**
     * @test
     */
    public function willDisplayOwnTalk()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        /* @var Authentication $auth */
        $auth = $this->app[Authentication::class];

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Submission',
            'description' => 'Make sure we can see our own talk.',
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'desired' => 0,
            'slides' => '',
            'other' => '',
            'sponsor' => '',
            'user_id' => $auth->user()->getId(),
        ];

        $this->setPost($talk_data);

        $speaker = m::mock(\OpenCFP\Application\Speakers::class);
        $speaker->shouldReceive('getTalk')->with(1)->andReturn($talk_data);
        $this->app['application.speakers'] = $speaker;
        $this->req->shouldReceive('get')->with('id')->andReturn(1);

        $response = $controller->viewAction($this->req);

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\Response::class,
            $response
        );
        $this->assertContains('Test Submission', (string) $response);
        $this->assertContains('Make sure we can see our own talk.', (string) $response);
    }

    /**
     * @test
     */
    public function canNotEditTalkAfterCfpIsClosed()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->req->shouldReceive('get')->with('id')->andReturn(4);

        $callForProposal = m::mock(CallForProposal::class);
        $callForProposal->shouldReceive('isOpen')->andReturn(false);
        $this->app['callforproposal'] = $callForProposal;
        $response = $controller->editAction($this->req);

        $flashMessage = $this->app['session']->get('flash');

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        $this->assertEquals('error', $flashMessage['type']);
        $this->assertEquals('You cannot edit talks once the call for papers has ended', $flashMessage['ext']);
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardOnEditWhenNoTalkID()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);
        $this->req->shouldReceive('get')->with('id')->andReturn('');

        $response = $controller->editAction($this->req);

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        $this->assertNotContains(
            '<input id="form-talk-title" type="text" name="title" class="form-control" placeholder="Talk Title"',
            (string) $response
        );
        $this->assertNotContains(
            '<div class="form-group">',
            (string) $response
        );
        $this->assertContains('dashboard', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function getRedirectedToDashboardWhenTalkIsNotYours()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);
        $this->req->shouldReceive('get')->with('id')->andReturn(1);
        $this->app['spot'] = m::mock(\Spot\Locator::class);
        $this->app['spot']->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\Talk::class)->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('where')->with(['id' => 1])->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('execute')->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('first')->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('toArray')->andReturn(['user_id'=> (int) $this->app[Authentication::class]->user()->getId() + 2]);

        $response = $controller->editAction($this->req);
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        $this->assertContains('dashboard', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);
        $this->req->shouldReceive('get')->with('id')->andReturn(1);
        $this->app['spot'] = m::mock(\Spot\Locator::class);
        $this->app['spot']->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\Talk::class)->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('where')->with(['id' => 1])->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('execute')->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('first')->andReturn($this->app['spot']);
        $this->app['spot']->shouldReceive('toArray')->andReturn(
            [
                'user_id' => (int) $this->app[Authentication::class]->user()->getId(),
                'title' => 'Title of talk to edit',
                'description' => 'The Description',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'other',
                'desired' => 0,
                'slides' => '',
                'other' => '',
                'sponsor' => '',
            ]
        );

        $response = $controller->editAction($this->req);
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\Response::class,
            $response
        );
        $this->assertContains(
            'Talk Title',
            (string) $response
        );
    }

    /**
     * @test
     */
    public function getDirectBackToEditPageWhenInValidTitle()
    {
        $this->asLoggedInSpeaker()
            ->get('/talk/create')
            ->assertSee('Create Your Talk')
            ->assertSuccessful();
    }
}
