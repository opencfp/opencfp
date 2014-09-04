<?php
use Mockery as m;

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
        $bootstrap = new \OpenCFP\Bootstrap();
        $app = $bootstrap->getApp();

        // Create an in-memory database for the test
        $cfg = new \Spot\Config;
        $cfg->addConnection('sqlite', [
            'dbname' => 'sqlite::memory',
            'driver' => 'pdo_sqlite'
        ]);
        $app['spot'] = new \Spot\Locator($cfg);

        // Create a test user
        $user_mapper = $app['spot']->mapper('OpenCFP\Entity\User');
        $user_mapper->migrate();
        $user = $user_mapper->build([
            'email' => 'test@test.com',
            'password' => 'randompasswordhashed',
            'first_name' => 'Test',
            'last_name' => 'User',
            'activated' => 1,
            'transportation' => 0,
            'hotel' => 0,
        ]);
        $user_mapper->save($user);
        $speaker_mapper = $app['spot']->mapper('OpenCFP\Entity\Speaker');
        $speaker_mapper->migrate();
        $speaker = $speaker_mapper->build([
            'user_id' => $user->id,
            'photo_path' => '/path/to/photo',
            'bio' => "This is speaker bio information",
            'info' => "This is additional speaker infi"
        ]);
        $speaker_mapper->save($speaker);

        // Create the favorite table
        $favorite_mapper = $app['spot']->mapper('OpenCFP\Entity\Favorite');
        $favorite_mapper->migrate();

        // Create a talk to display
        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $talk_mapper->migrate();
        $talk = $talk_mapper->build([
            'title' => 'Test Title',
            'description' => 'Test title description',
            'user_id' => 1,
            'type' => 'regular',
            'category' => 'testing',
            'level' => 'beginner',
            'desired' => 1,
            'slides' => 'slides',
            'other' => 'other',
            'sponsor' => 1,
            'favorite' => 0,
            'selected' => 0,
        ]);
        $talk_mapper->save($talk);

        // Set things up so Sentry believes we're logged in
        $user_mock = m::mock('StdClass');
        $user_mock->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user_mock->shouldReceive('getId')->andReturn(1);

        // Create a test double for Sentry
        $sentry = m::mock('StdClass');
        $sentry->shouldReceive('check')->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user_mock);
        $app['sentry'] = $sentry;

        // Create a fake request object
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('get')->with('page')->andReturn($user->id);

        // Instantiate the controller and run the indexAction
        $controller = new \OpenCFP\Controller\DashboardController();
        $response = $controller->indexAction($req, $app);
        $this->assertContains('Test Title', $response);
        $this->assertContains('Test User', $response);
    }
}
