<?php
namespace OpenCFP\Tests\Http\Controller\Admin;

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SpeakersControllerTest extends \PHPUnit_Framework_TestCase
{
    public $app;

    protected function setUp()
    {
        // Create our Application object
        $this->app = new Application(BASE_PATH, Environment::testing());

        // Create a test double for our User entity
        $user = m::mock(\OpenCFP\Domain\Entity\User::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);

        // Create a test double for our Sentry object
        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('check')->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $this->app['sentry'] = $sentry;
        $this->app['user'] = $user;
    }

    /**
     * Verify that not found speaker redirects and sets flash error message
     *
     * @test
     */
    public function speakerNotFoundHasFlashMessage()
    {
        $speakerId = uniqid();

        // Override our mapper with the double
        $spot = m::mock('Spot\Locator');
        $mapper = m::mock(\OpenCFP\Domain\Entity\Mapper\User::class);
        $mapper->shouldReceive('get')
            ->andReturn([]);

        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\User::class)
            ->andReturn($mapper);
        $this->app['spot'] = $spot;

        // Create a session object
        $this->app['session'] = new Session(new MockFileSessionStorage);

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        // Create our Request object
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('get')->with('id')->andReturn($speakerId);

        // Execute the controller and capture the output
        $controller = new \OpenCFP\Http\Controller\Admin\SpeakersController();
        $controller->setApplication($this->app);
        $response = $controller->viewAction($req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );

        $this->assertContains(
            'Could not find requested speaker',
            $this->app['session']->get('flash')
        );
    }
}
