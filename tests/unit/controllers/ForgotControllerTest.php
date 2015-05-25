<?php
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ForgotControllerTest extends PHPUnit_Framework_TestCase
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
        $app['session'] = new Session(new MockFileSessionStorage());
        $app['form.csrf_provider'] = new SessionCsrfProvider($app['session'], 'secret');
        ob_start();
        $app->run();
        ob_end_clean();

        $controller = new OpenCFP\Http\Controller\ForgotController();
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
