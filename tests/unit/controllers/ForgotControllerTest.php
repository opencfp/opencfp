<?php
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ForgotControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that index action displays a form that allows the user to reset
     * their password
     *
     * @test
     * @runInSeparateProcess
     */
    public function indexDisplaysCorrectForm()
    {
        $app = new Application(BASE_PATH, Environment::testing());

        $controller = new OpenCFP\Http\Controller\ForgotController($app);
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
