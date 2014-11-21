<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;

class ForgotControllerTest extends PHPUnit_Framework_TestCase
{
    public $app;
    public $req;

    public function setup()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());

        $session = m::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('start')->andReturn(true);
        $session->shouldReceive('getId')->andReturn(uniqid());
        $session->shouldReceive('get');

        $this->app['session'] = $session;
        $this->req = m::mock('Symfony\Component\HttpFoundation\Request');
    }
    /**
     * Test that index action displays a form that allows the user to reset
     * their password
     *
     * @test
     */
    public function indexDisplaysCorrectForm()
    {
        $controller = new OpenCFP\Http\Controller\ForgotController($this->app);
        $response = $controller->indexAction();

        // Get the form object and verify things look correct
        $this->assertContains(
            '<form id="forgot"',
            (string)$response
        );
        $this->assertContains(
            '<input type="hidden" id="forgot__token"',
            (string)$response
        );
        $this->assertContains(
            '<input id="form-forgot-email"',
            (string)$response
        );
    }
}
