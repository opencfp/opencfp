<?php
use Mockery as m;

class ForgotControllerTest extends PHPUnit_Framework_TestCase
{
    public $app;
    public $req;

    public function setup()
    {
        $bootstrap = new OpenCFP\Bootstrap();
        $this->app = $bootstrap->getApp();
        $session = m::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('start')->andReturn(true);
        $session->shouldReceive('getId')->andReturn(uniqid());

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
        $controller = new OpenCFP\Controller\ForgotController();
        $response = $controller->indexAction($this->req, $this->app);

        // Get the form object and verify things look correct
        $this->assertContains(
            '<form id="forgot"',
            $response
        );
        $this->assertContains(
            '<input type="hidden" id="forgot__token"',
            $response
        );
        $this->assertContains(
            '<input id="form-forgot-email"',
            $response
        );
    }
}
