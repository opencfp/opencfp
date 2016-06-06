<?php

namespace OpenCFP\Test\Http\Controller;

use OpenCFP\Application;
use OpenCFP\Environment;
use Silex\Provider\CsrfServiceProvider;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

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
//        unset($app['session']);
//        $app['session'] = new Session(new MockFileSessionStorage());
//        unset($app['csrf.token_storage']);
//        unset($app['csrf.token_manager']);
//        unset($app['csrf.token_generator']);
//        unset($app['session.storage']);
//        unset($app['session.storage.handler']);
//        unset($app['session.storage.native']);
        $app['form.csrf_provider'] = new SessionCsrfProvider($app['session'], 'secret');
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
            '<input type="hidden" id="forgot__token"',
            (string) $response
        );
        $this->assertContains(
            '<input id="form-forgot-email"',
            (string) $response
        );
    }
}
