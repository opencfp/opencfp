<?php

namespace OpenCFP\Http\Controller;

use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Http\Form\ResetForm;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Http\Form\ForgotForm;

/**
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
class ForgotController extends BaseController
{
    use FlashableTrait;

    public function indexAction()
    {
        $form = $this->app['form.factory']->create(new ForgotForm());

        $data = array(
            'form' => $form->createView(),
            'current_page' => "Forgot Password"
        );

        return $this->render('user/forgot_password.twig', $data);
    }

    public function sendResetAction(Request $req)
    {
        $form = $this->app['form.factory']->create(new ForgotForm());
        $form->bind($req);

        if (!$form->isValid()) {
            $this->app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Please enter a properly formatted email address"
            ));

            return $this->redirectTo('forgot_password');
        }

        // Check to make sure they actually exist in the system...
        $data = $form->getData();

        try {
            $user = $this->app['sentry']->getUserProvider()->findByLogin($data['email']);
        } catch (UserNotFoundException $e) {
            $this->app['session']->set('flash', $this->successfulSendFlashParameters($data['email']));

            return $this->redirectTo('forgot_password');
        }

        // Create a reset code and email the URL to our user
        $reset_code = $user->getResetPasswordCode();
        $response = $this->sendResetEmail($this->app, $user->getId(), $data['email'], $reset_code);

        if ($response == false) {
            $this->app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "We were unable to send your password reset request. Please try again"
            ));

            return $this->redirectTo('forgot_password');
        }

        $this->app['session']->set('flash', $this->successfulSendFlashParameters($data['email']));

        return $this->redirectTo('login');
    }

    public function resetAction(Request $req)
    {
        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;
        try {
            $user = $this->app['sentry']->getUserProvider()->findById($req->get('user_id'));

            if (!$user->checkResetPasswordCode($req->get('reset_code'))) {
                $error++;
            }
        } catch (UserNotFoundException $e) {
            $error++;
        }

        if ($error > 0) {
            $this->app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => $errorMessage,
            ));
        }

        // Build password form and display it to the user
        $form_options = array(
            'user_id' => $req->get('user_id'),
            'reset_code' => $req->get('reset_code')
        );
        $form = $this->app['form.factory']->create(new ResetForm(), $form_options);

        $data['form'] = $form->createView();
        $data['flash'] = $this->getFlash($this->app);

        return $this->render('user/forgot_password.twig', $data);
    }

    public function processResetAction(Request $req)
    {
        $user_id = $req->get('user_id');
        $reset_code = $req->get('reset_code');
        $form_options = array(
            'user_id' => $user_id,
            'reset_code' => $reset_code
        );
        $form = $this->app['form.factory']->create(new ResetForm(), $form_options);

        if (! $form->isValid()) {
            return $this->render('user/reset_password.twig', ['form' => $form->createView()]);
        }

        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;

        try {
            $user = $this->app['sentry']->getUserProvider()->findById($req->get('user_id'));
        } catch (UserNotFoundException $e) {
            $error++;
        }

        if (! $user->checkResetPasswordCode($req->get('reset_code'))) {
            $error++;
        }

        if ($error > 0) {
            $this->app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => $errorMessage,
            ));
        }

        return $this->redirectTo('forgot_password');
    }

    public function updatePasswordAction(Request $req)
    {
        $postArray = $req->request->all();

        $user_id = $postArray['reset']['user_id'];
        $reset_code = $postArray['reset']['reset_code'];
        $password = $postArray['reset']['password']['password'];

        try {
            $user = $this->app['sentry']->getUserProvider()->findById($user_id);
        } catch (UserNotFoundException $e) {
            echo $e;
            die();
        }

        /**
         * Can't let people replace their passwords with one they have
         * already
         */
        if ($user->checkPassword($password) === true) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Please select a different password than your current one.",
            ]);

            return $this->redirectTo('login');
        }

        // Everything looks good, let's actually reset their password
        if ($user->attemptResetPassword($reset_code, $password)) {
            $this->app['session']->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => "You've successfully reset your password.",
            ]);

            return $this->redirectTo('login');
        }

        // user may have tried using the recovery twice
        $this->app['session']->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => "Password reset failed, please contact the administrator.",
        ]);

        return $this->redirectTo('homepage');
    }

    protected function sendResetEmail(Application $app, $user_id, $email, $reset_code)
    {
        // Here to cover possible errors from refactor. Should be substituted appropriately below.
        $twig = $app['twig'];

        // Build our email that we will send
        $template = $twig->loadTemplate('emails/reset_password.twig');
        $parameters = array(
            'reset_code' => $reset_code,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            'user_id' => $user_id,
            'email' => $app->config('application.email'),
            'title' => $app->config('application.title')
        );

        try {
            $mailer = $app['mailer'];
            $message = new \Swift_Message();

            $message->setTo($email);
            $message->setFrom(
                $template->renderBlock('from', $parameters),
                $template->renderBlock('from_name', $parameters)
            );

            $message->setSubject($template->renderBlock('subject', $parameters));
            $message->setBody($template->renderBlock('body_text', $parameters));
            $message->addPart(
                $template->renderBlock('body_html', $parameters),
                'text/html'
            );

            return $mailer->send($message);
        } catch (\Exception $e) {
            echo $e;
            die();
        }
    }

    protected function successfulSendFlashParameters($email)
    {
        return array(
            'type' => 'success',
            'short' => 'Success',
            'ext' => "If your email was valid, we sent a link to reset your password to $email"
        );
    }
}
