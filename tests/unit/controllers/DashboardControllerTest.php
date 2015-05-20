<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class DashboardControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that the index page returns a list of talks associated
     * with a specific user and information about that user as well
     *
     * @test
     */
    public function indexDisplaysUserAndTalks()
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session'] = new Session(new MockFileSessionStorage());

        // Set things up so Sentry believes we're logged in
        $user = m::mock('StdClass');
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);

        // Create a test double for Sentry
        $sentry = m::mock('StdClass');
        $sentry->shouldReceive('check')->times(3)->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $app['sentry'] = $sentry;

        // Create a test double for a talk in profile
        $talk = m::mock('StdClass');
        $talk->shouldReceive('title')->andReturn('Test Title');
        $talk->shouldReceive('id')->andReturn(1);
        $talk->shouldReceive('type', 'category', 'created_at');

        // Create a test double for profile
        $profile = m::mock('StdClass');
        $profile->shouldReceive('name')->andReturn('Test User');
        $profile->shouldReceive('photo', 'company', 'twitter', 'airport', 'bio', 'info', 'transportation', 'hotel', 'token');
        $profile->shouldReceive('talks')->andReturn([$talk]);

        $speakerService = m::mock('StdClass');
        $speakerService->shouldReceive('findProfile')->andReturn($profile);

        $app['application.speakers'] = $speakerService;

        ob_start();
        $app->run();  // Fire before handlers... boot...
        ob_end_clean();

        // Instantiate the controller and run the indexAction
        $controller = new \OpenCFP\Http\Controller\DashboardController($app);
        $response = $controller->showSpeakerProfile();
        $this->assertContains('Test Title', (string)$response);
        $this->assertContains('Test User', (string)$response);
    }
}
