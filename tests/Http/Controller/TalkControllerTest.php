<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Sentry;
use DateTime;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\TalkController;

class TalkControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $req;

    protected function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
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
        
        $this->app['spot'] = $spot;

        // Initialize the talk table in the sqlite database
        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk_mapper->migrate();

        // Set things up so Sentry believes we're logged in
        $user = m::mock('StdClass');
        $user->shouldReceive('getId')->andReturn(uniqid());
        $user->shouldReceive('getLogin')->andReturn(uniqid() . '@grumpy-learning.com');

        // Create a test double for Sentry
        $sentry = m::mock(Sentry::class);
        $sentry->shouldReceive('check')->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $this->app['sentry'] = $sentry;

        // Create a test double for sessions so we can control what happens
        $this->app['session'] = new SessionDouble();

        // Create our test double for the request object
        $this->req = m::mock('Symfony\Component\HttpFoundation\Request');
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
        $swiftmailer = m::mock('StdClass');
        $swiftmailer->shouldReceive('send')->andReturn(true);
        $this->app['mailer'] = $swiftmailer;

        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Title With Ampersand',
            'description' => "The title should contain this & that",
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'desired' => 0,
            'slides' => '',
            'other' => '',
            'sponsor' => '',
            'user_id' => $sentry->getUser()->getId(),
            'tags' => '',
        ];

        $this->setPost($talk_data);

        /**
         * If the talk was successfully created, a success value is placed
         * into the session flash area for display
         */
        $controller->processCreateAction($this->req);

        $create_flash = $this->app['session']->get('flash');
        $this->assertEquals($create_flash['type'], 'success');

        // Now, edit the results and update them
        $talk_data['id'] = 1;
        $talk_data['description'] = "The title should contain this & that & this other thing";
        $talk_data['title'] = "Test Title With Ampersand & More Things";
        $this->setPost($talk_data);

        $controller->updateAction($this->req, $this->app);
        $update_flash = $this->app['session']->get('flash');
        $this->assertEquals($update_flash['type'], 'success');
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

        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];

        // Get our request object to return expected data
        $talk_data = [
            'title' => 'Test Submission',
            'description' => "Make sure we can submit before end and not after.",
            'type' => 'regular',
            'level' => 'entry',
            'category' => 'other',
            'desired' => 0,
            'slides' => '',
            'other' => '',
            'sponsor' => '',
            'user_id' => $sentry->getUser()->getId(),
            'tags' => '',
        ];

        $this->setPost($talk_data);

        // Set CFP end to today (whenever test is run)
        // Previously, this fails because it checked midnight
        // for the current date. `isCfpOpen` now uses 11:59pm current date.
        $now = new DateTime();

        $config = $this->app['config'];
        $config['application']['enddate'] = $now->format('M. jS, Y');
        $this->app['config'] = $config;

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
        $yesterday = new DateTime("yesterday");

        $config = $this->app['config'];
        $config['application']['enddate'] = $yesterday->format('M. jS, Y');
        $this->app['config'] = $config;

        $controller->createAction($this->req);

        $flashMessage = $this->app['session']->get('flash');
        $this->assertEquals('error', $flashMessage['type']);
        $this->assertEquals('You cannot create talks once the call for papers has ended', $flashMessage['ext']);
    }
}
