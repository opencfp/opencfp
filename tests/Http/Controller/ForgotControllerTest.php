<?php

namespace OpenCFP\Test\Http\Controller;

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\Session\Session;

class ForgotControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that index action displays a form that allows the user to reset
     * their password
     *
     * @test
     */
    public function indexDisplaysCorrectForm()
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session.test'] = true;
        ob_start();
        $app->run();
        ob_end_clean();

        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($app);
        $response = $controller->indexAction();

        // Get the form object and verify things look correct
        $this->assertContains(
            '<form id="forgot"',
            (string) $response
        );
        $this->assertContains(
            '<input type="hidden" id="forgot_form__token"',
            (string) $response
        );
        $this->assertContains(
            '<input type="email" id="forgot_form_email"',
            (string) $response
        );
    }
}
