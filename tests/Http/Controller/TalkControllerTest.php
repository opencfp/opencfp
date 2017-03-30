<?php

namespace OpenCFP\Test\Http\Controller;

use DateTime;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Entity\TalkMeta;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\TalkController;
use OpenCFP\Http\Form\Entity\Talk;
use OpenCFP\Util\Wrapper\SentinelWrapper;

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

        list($user, $sentinel) = $this->createLoggedInUser();
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $sentinel;

        // Create a test double for sessions so we can control what happens
        unset($this->app['session']);
        $this->app['session'] = new SessionDouble();

        $this->app['callforproposal'] = m::mock(CallForProposal::class);
        $this->app['callforproposal']->shouldReceive('isOpen')->andReturn(true);

        // Create our test double for the request object
        $this->req = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->req->shouldReceive('getMethod');
    }

    public function tearDown()
    {
        m::close();
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
            'user_id' => $this->app['sentinel']->getUser()['id'],
        ];

        $this->setPost($talk_data);

        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new DateTime();

        $this->app['callforproposal'] = new CallForProposal(new DateTime($now->format('M. jS, Y')));

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
        $yesterday = new DateTime('yesterday');

        $this->app['callforproposal'] = new CallForProposal(new DateTime($yesterday->format('M. jS, Y')));

        $controller->createAction($this->req);

        $flashMessage = $this->app['session']->get('flash');
        $this->assertEquals('error', $flashMessage['type']);
        $this->assertEquals('You cannot create talks once the call for papers has ended', $flashMessage['ext']);
    }

    /**
     * @test
     */
    public function viewKicksOutUsersWhoAreNotLoggedIn()
    {
        // We need a Sentinel user who is not logged in
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $this->createNotLoggedInUser();

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /login',
            $controller->viewAction($this->req)->getContent(),
            'Non-logged in user can view a talk'
        );
    }

    /**
     * @test
     */
    public function viewRendersTalkForLoggedInUser()
    {
        list($talk, $talk_id) = $this->createTalk();

        // Create a double for our speaker object
        $application_speakers = m::mock('\stdClass');
        $application_speakers->shouldReceive('getTalk')->with($talk_id)->andReturn($talk);
        $this->app['application.speakers'] = $application_speakers;

        // Tell our request object what the ID of the talk is
        $this->req->shouldReceive('get')->with('id')->andReturn($talk_id);

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            '<!-- id: talk/view -->',
            $controller->viewAction($this->req)->getContent(),
            'TalkController::viewAction did not correctly render view'
        );
    }

    /**
     * @test
     */
    public function editKicksYouOutIfNotLoggedIn()
    {
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $this->createNotLoggedInUser();
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /login',
            $controller->editAction($this->req)->getContent(),
            'editAction did not kick out a non-logged-in user'
        );
    }

    /**
     * @test
     */
    public function editDisplaysTalkFormCorrectly()
    {
        // Get our logged in user
        list($user, $sentinel) = $this->createLoggedInUser();
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $sentinel;

        list($talk, $talk_id) = $this->createTalk($user['id']);

        $this->req->shouldReceive('get')->with('id')->andReturn($talk_id);
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            '<!-- id: form/talk -->',
            $controller->editAction($this->req)->getContent(),
            'edit form did not display expected talk'
        );
    }

    /**
     * @test
     */
    public function editHandlesMissingTalkId()
    {
        list($user, $sentinel) = $this->createLoggedInUser();
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $sentinel;

        $this->req->shouldReceive('get')->with('id');
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /dashboard',
            $controller->editAction($this->req)->getContent(),
            'edit form did not handle missing talk ID correctly'
        );
    }

    /**
     * @test
     */
    public function editWillNotLetYouEditTalksIfCfpIsNotOpen()
    {
        // Get our logged in user
        list($user, $sentinel) = $this->createLoggedInUser();
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $sentinel;

        $this->app['callforproposal'] = $this->createClosedCfp();

        list($talk, $talk_id) = $this->createTalk($user['id']);

        $this->req->shouldReceive('get')->with('id')->andReturn($talk_id);
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /talk',
            $controller->editAction($this->req)->getContent(),
            'editAction allowed a talk to be edited after the CfP was closed'
        );
    }

    /**
     * @test
     */
    public function createTalkKicksOutNonLoggedInUsers()
    {
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $this->createNotLoggedInUser();
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /login',
            $controller->createAction($this->req)->getContent(),
            'createAction did not kick out non-logged-in user'
        );
    }

    /**
     * @test
     */
    public function cannotCreateTalksIfCfpIsClosed()
    {
        $this->app['callforproposal'] = $this->createClosedCfp();

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /dashboard',
            $controller->createAction($this->req)->getContent(),
            'createAction let you create a talk after the CfP is closed'
        );
    }

    /**
     * @test
     */
    public function createTalksShowsForm()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            '<!-- id: form/talk -->',
            $controller->createAction($this->req)->getContent(),
            'createAction did not show talk form'
        );
    }

    /**
     * @test
     */
    public function processCreateTalkKicksYouOutForNotBeingLoggedIn()
    {
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $this->createNotLoggedInUser();
        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /login',
            $controller->processCreateAction($this->req)->getContent(),
            'processCreateAction did not kick out an unauthenticated user'
        );
    }

    /**
     * @test
     */
    public function processCreateRefusesNewTalksAfterCfpCloses()
    {
        unset($this->app['callforproposal']);
        $this->app['callforproposal'] = $this->createClosedCfp();

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /dashboard',
            $controller->processCreateAction($this->req)->getContent(),
            'processCreateAction allowed creating talks after the CfP is closed'
        );
    }

    /**
     * @test
     */
    public function processCreateHandlesInvalidForms()
    {
        // Build up a bad form
        $form_data = ['title' => null, 'description' => "We're missing a title"];
        $this->setPost($form_data);

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            '<!-- id: form/talk -->',
            $controller->processCreateAction($this->req)->getContent(),
            'processCreate did not handle an invalid form correctly'
        );
    }

    /**
     * @test
     */
    public function processCreateWorksAsExpected()
    {
        $talk = m::mock(Talk::class)->makePartial();
        $form = m::mock('\stdClass');
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('handleRequest');
        $form->shouldReceive('getData')->andReturn($talk);
        $form_factory = m::mock('\stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($form);
        $this->app['form.factory'] = $form_factory;

        $expected_flash = [
            'type' => 'success',
            'short' => 'Success',
            'ext' => 'Successfully saved talk.',
        ];
        $controller = new TalkController();
        $controller->setApplication($this->app);
        $controller->processCreateAction($this->req);

        $this->assertEquals(
            $expected_flash,
            $this->app['session']->get('flash'),
            'processCreate did not handle a valid talk form correctly'
        );
    }

    /**
     * @test
     */
    public function updateKicksOutUnauthenticatedUsers()
    {
        unset($this->app['sentinel']);
        $this->app['sentinel'] = $this->createNotLoggedInUser();

        $controller = new TalkController();
        $controller->setApplication($this->app);

        $this->assertContains(
            'Redirecting to /login',
            $controller->updateAction($this->req)->getContent(),
            'updateAction did not kick out unauthenticated users'
        );
    }

    /**
     * @test
     */
    public function updateHandlesValidFormCorrectly()
    {
        list($talk, $talk_id) = $this->createTalk();
        $talk_form_entity = new Talk();
        $talk_form_entity->createFromArray([
            'id' => $talk_id,
            'title' => uniqid(),
            'description' => $talk->description,
            'user_id' => $talk->user_id,
            'type' => 'regular',
            'category' => 'test',
            'level' => 'beginner',
            'desired' => 0,
            'sponsor' => 0,
            'other' => 'OTHER',
            'slides' => null,
        ]);
        $form = m::mock('\stdClass');
        $form->shouldReceive('handleRequest')->with($this->req);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->andReturn($talk_form_entity);
        $form_factory = m::mock('\stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($form);
        unset($this->app['form.factory']);
        $this->app['form.factory'] = $form_factory;

        $controller = new TalkController();
        $controller->setApplication($this->app);
        $controller->updateAction($this->req);
        $flash = $this->app['session']->get('flash');

        $this->assertEquals(
            'Successfully saved talk.',
            $flash['ext'],
            'updateAction did not save updated talk'
        );
    }

    /**
     * @test
     */
    public function updateHandlesInvalidFormCorrectly()
    {
        $controller = new TalkController();
        $controller->setApplication($this->app);
        $response = $controller->updateAction($this->req);
        $this->assertContains(
            'Please check your form for errors',
            $response->getContent(),
            'TalkController::updateAction did not handle invalid form correctly'
        );
    }

    /**
     * @return array
     */
    protected function createLoggedInUser()
    {
        $user = [];
        $user['id'] = random_int(1, 1000);
        $user['email'] = uniqid() . '@opencfp.org';

        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn($user);
        $sentinel->shouldReceive('getUser')->andReturn($user);

        return [$user, $sentinel];
    }

    /**
     * @return m\MockInterface|\Yay_MockObject
     */
    protected function createNotLoggedInUser()
    {
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(false);

        return $sentinel;
    }

    /**
     * @param int $user_id
     * @return array
     */
    protected function createTalk($user_id = 1)
    {
        $mapper = $this->app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $data = [
            'user_id' => $user_id,
            'title' => 'Test Title',
            'description' => 'Test Description',
            'other' => '',
            'sponsor' => '',
            'desired' => '',
            'slides' => '',
            'type' => 'regular',
            'category' => 'test',
            'level' => 'beginner',
        ];
        $talk = $mapper->build($data);
        $talk_id = $mapper->save($talk);

        return [$talk, $talk_id];
    }

    /**
     * @return m\MockInterface|\Yay_MockObject
     */
    protected function createClosedCfp()
    {
        $cfp = m::mock('\stdClass');
        $cfp->shouldReceive('isOpen')->andReturn(false);

        return $cfp;
    }
}
