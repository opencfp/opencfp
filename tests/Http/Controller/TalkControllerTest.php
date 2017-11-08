<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Entity\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\TalkController;

/**
 * Class TalkControllerTest
 * @package OpenCFP\Test\Http\Controller
 * @group db
 */
class TalkControllerTest extends \PHPUnit\Framework\TestCase
{
    private $app;
    private $req;

    protected function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $this->app['session.test'] = true;
        ob_start();
        $this->app->run();
        ob_end_clean();

        // Override things so that Spot2 is using in-memory tables
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite',
        ]);
        $spot = new \Spot\Locator($cfg);

        unset($this->app['spot']);
        $this->app['spot'] = $spot;

        // Initialize the talk table in the sqlite database
        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk_mapper->migrate();

        /*
         * Need to include all of the relationships for a talk now since we
         * have modified looking up a talk to include "with"
         */
        $favorites_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Favorite::class);
        $favorites_mapper->migrate();

        $talk_comments_mapper = $spot->mapper(\OpenCFP\Domain\Entity\TalkComment::class);
        $talk_comments_mapper->migrate();

        $talk_meta_mapper = $spot->mapper(TalkMeta::class);
        $talk_meta_mapper->migrate();

        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(uniqid());
        $user->shouldReceive('getLogin')->andReturn(uniqid() . '@grumpy-learning.com');

        // Create a test double for Sentry
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        unset($this->app[Authentication::class]);
        $this->app[Authentication::class] = $auth;

        $this->app['callforproposal'] = m::mock(CallForProposal::class);
        $this->app['callforproposal']->shouldReceive('isOpen')->andReturn(true);

        // Create our test double for the request object
        $this->req = m::mock(\Symfony\Component\HttpFoundation\Request::class);
    }

    /**
     * Verify that talks with ampersands and other characters in them can
     * be created and then edited properly
     *
     * @test
     */
    public function ampersandsAcceptableCharacterForTalks()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        // Create a test double for SwiftMailer
        $swiftmailer = m::mock(\stdClass::class);
        $swiftmailer->shouldReceive('send')->andReturn(true);
        $this->app['mailer'] = $swiftmailer;

        /* @var Authentication $auth */
        $auth = $this->app[Authentication::class];

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Title With Ampersand',
            'description' => 'The title should contain this & that',
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

        /**
         * If the talk was successfully created, a success value is placed
         * into the session flash area for display
         */
        $controller->processCreateAction($this->req);

        $create_flash = $this->app['session']->get('flash');
        $this->assertEquals($create_flash['type'], 'success');
    }

    /**
     * Method for setting the values that would be posted to a controller
     * action
     *
     * @param  mixed $data
     * @return void
     */
    protected function setPost($data)
    {
        foreach ($data as $key => $value) {
            $this->req->shouldReceive('get')->with($key)->andReturn($value);
        }
    }

    /**
     * @test
     */
    public function allowSubmissionsUntilRightBeforeMidnightDayOfClose()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        /* @var Authentication $auth */
        $auth = $this->app[Authentication::class];

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Submission',
            'description' => 'Make sure we can submit before end and not after.',
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

        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new \DateTime();

        $this->app['callforproposal'] = new CallForProposal(new \DateTime($now->format('M. jS, Y')));

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
            (string)$response
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
        $this->app['spot']->shouldReceive('toArray')->andReturn(['user_id'=> (int)$this->app[Authentication::class]->user()->getId() + 2]);

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
                'user_id' => (int)$this->app[Authentication::class]->user()->getId(),
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
            (string)$response
        );
    }

    /**
     * @test
     */
    public function getDirectBackToEditPageWhenInValidTitle()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $talk_data = [
            'id' => 3,
            'title' => '',
            'description' => 'This talk is missing its title',
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'desired' => 0,
            'slides' => '',
            'other' => '',
            'sponsor' => '',
            'user_id' => $this->app[Authentication::class]->user()->getId(),
        ];

        $this->setPost($talk_data);

        $response = $controller->updateAction($this->req);

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\Response::class,
            $response
        );
        $this->assertContains('Please fill in the title', (string) $response);
    }
}
