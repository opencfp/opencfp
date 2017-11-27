<?php

namespace OpenCFP\Test\Integration\Http\Controller;

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Environment;
use OpenCFP\Infrastructure\Auth\UserInterface;

/**
 * @group db
 * @coversNothing
 */
class ForgotControllerTest extends \PHPUnit\Framework\TestCase
{
    protected $app;

    protected function setUp()
    {
        $this->app                 = new Application(BASE_PATH, Environment::testing());
        $this->app['session.test'] = true;
        \ob_start();
        $this->app->run();
        \ob_end_clean();
    }

    /**
     * Test that index action displays a form that allows the user to reset
     * their password
     *
     * @test
     */
    public function indexDisplaysCorrectForm()
    {
        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($this->app);
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

    /**
     * @test
     */
    public function sendResetDisplaysCorrectMessage()
    {
        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andReturn($this->createUser());
        $this->app[AccountManagement::class] = $accounts;

        // Override our reset_emailer service
        $reset_emailer = m::mock(\OpenCFP\Provider\ResetEmailerServiceProvider::class);
        $reset_emailer->shouldReceive('send')->andReturn(true);
        $this->app['reset_emailer'] = $reset_emailer;

        // We need to create a replacement form.factory to return a form we control
        $form_factory = m::mock(\Silex\Provider\FormServiceProvider::class);
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->app['form.factory'] = $form_factory;

        $req        = m::mock(\Symfony\Component\HttpFoundation\Request::class);
        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($this->app);
        $controller->sendResetAction($req);

        // As long as the email validates as being a potential email, the flash message should indicate success
        $flash_message = $this->app['session']->get('flash');
        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $flash_message['ext']
        );
    }

    /**
     * @test
     */
    public function invalidResetFormTriggersErrorMessage()
    {
        $form_factory = m::mock(\Silex\Provider\FormServiceProvider::class);
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('not valid'));
        $this->app['form.factory'] = $form_factory;

        $req        = m::mock(\Symfony\Component\HttpFoundation\Request::class);
        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($this->app);
        $controller->sendResetAction($req);

        $flash_message = $this->app['session']->get('flash');
        $this->assertContains(
            'Please enter a properly formatted email address',
            $flash_message['ext']
        );
    }

    /**
     * @test
     */
    public function resetPasswordNotFindingUserCorrectlyDisplaysMessage()
    {
        $form_factory = m::mock(\Silex\Provider\FormServiceProvider::class);
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->app['form.factory'] = $form_factory;

        $req        = m::mock(\Symfony\Component\HttpFoundation\Request::class);
        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($this->app);
        $controller->sendResetAction($req);

        $flash_message = $this->app['session']->get('flash');
        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $flash_message['ext']
        );
    }

    /**
     * @test
     */
    public function resetPasswordHandlesNotSendingResetEmailCorrectly()
    {
        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andReturn($this->createUser());
        $this->app[AccountManagement::class] = $accounts;

        // Override our reset_emailer service
        $reset_emailer = m::mock(\OpenCFP\Provider\ResetEmailerServiceProvider::class);
        $reset_emailer->shouldReceive('send')->andReturn(false);
        $this->app['reset_emailer'] = $reset_emailer;

        // We need to create a replacement form.factory to return a form we control
        $form_factory = m::mock(\Silex\Provider\FormServiceProvider::class);
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->app['form.factory'] = $form_factory;

        $req        = m::mock(\Symfony\Component\HttpFoundation\Request::class);
        $controller = new \OpenCFP\Http\Controller\ForgotController();
        $controller->setApplication($this->app);
        $controller->sendResetAction($req);

        // As long as the email validates as being a potential email, the flash message should indicate success
        $flash_message = $this->app['session']->get('flash');
        $this->assertContains(
            'We were unable to send your password reset request. Please try again',
            $flash_message['ext']
        );
    }

    private function createUser(): UserInterface
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getResetPasswordCode');
        $user->shouldReceive('getId');

        return $user;
    }

    private function createForm($valid_status): \OpenCFP\Http\Form\ForgotForm
    {
        $is_valid = ($valid_status == 'valid');
        $form     = m::mock(\OpenCFP\Http\Form\ForgotForm::class);
        $form->shouldReceive('handleRequest');
        $form->shouldReceive('isValid')->andReturn($is_valid);
        $data = ['email' => 'test@opencfp.org'];
        $form->shouldReceive('getData')->andReturn($data);

        return $form;
    }
}
