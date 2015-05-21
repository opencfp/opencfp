<?php

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;

class TalkControllerTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $req;

    public function setup()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());

        // Override things so that Spot2 is using in-memory tables
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite'
        ]);
        $this->app['spot'] = new \Spot\Locator($cfg);

        // Initialize the talk table in the sqlite database
        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk_mapper->migrate();

        // Set things up so Sentry believes we're logged in
        $user = m::mock('StdClass');
        $user->shouldReceive('getId')->andReturn(uniqid());
        $user->shouldReceive('getLogin')->andReturn(uniqid() . '@grumpy-learning.com');

        // Create a test double for Sentry
        $sentry = m::mock('StdClass');
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
        $controller = new OpenCFP\Http\Controller\TalkController();
        $controller->setApplication($this->app);

        // Create a test double for SwiftMailer
        $swiftmailer = m::mock('StdClass');
        $swiftmailer->shouldReceive('send')->andReturn(true);
        $this->app['mailer'] = $swiftmailer;

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
            'user_id' => $this->app['sentry']->getUser()->getId()
        ];

        $this->setPost($talk_data);

        /**
         * If the talk was successfully created, a success value is placed
         * into the session flash area for display
         */
        $create_response = $controller->processCreateAction($this->req);
        $create_flash = $this->app['session']->get('flash');
        $this->assertEquals($create_flash['type'], 'success');

        // Now, edit the results and update them
        $talk_data['id'] = 1;
        $talk_data['description'] = "The title should contain this & that & this other thing";
        $talk_data['title'] = "Test Title With Ampersand & More Things";
        $this->setPost($talk_data);

        $update_response = $controller->updateAction($this->req, $this->app);
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
}

class SessionDouble extends Symfony\Component\HttpFoundation\Session\Session
{
    protected $flash;

    public function get($value, $default = null)
    {
        return $this->$value;
    }

    public function set($name, $value)
    {
        $this->$name = $value;
    }
}
