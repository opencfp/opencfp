<?php
namespace OpenCFP\Controller;

use OpenCFP\Config\ConfigINIFileLoader;
use OpenCFP\Model\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ForgotController
{
    public function indexAction(Request $req, Application $app)
    {
        $form = $app['form.factory']->create(new \OpenCFP\Form\ForgotForm());
        $template = $app['twig']->loadTemplate('forgot_index.twig');

        return $template->render(array('form' => $form->createView()));
    }

    public function sendResetAction(Request $req, Application $app)
    {
        $form = $app['form.factory']->create(new \OpenCFP\Form\ForgotForm());
        $form->bind($req);

        if (!$form->isValid()) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "Please enter a properly formatted email address."
            ));
            
            return $app->redirect($app['url'] . '/forgot');
        } 

        // Check to make sure they actually exist in the system...
        $data = $form->getData();

        try {
            $user = $app['sentry']->getUserProvider()->findByLogin($data['email']);
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "We couldn't find a user with that email"
            ));
            
            return $app->redirect($app['url'] . '/forgot');
        }

        // Create a reset code and email the URL to our user
        $reset_code = $user->getResetPasswordCode();
        $response = $this->sendResetEmail($app['twig'], $user->getId(), $data['email'], $reset_code);

        if ($response == false) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "We were unable to send your password reset request. Please try again."
            ));
            
            return $app->redirect($app['url'] . '/forgot');
        }

        return $app->redirect($app['url'] . '/forgot_success');
    }

    public function successAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('forgot_success.twig');
        
        return $template->render(array());
    }

    public function resetAction(Request $req, Application $app)
    {
        try {
            $user = $app['sentry']->getUserProvider()->findById($req->get('user_id'));
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            $template = $app['twig']->loadTemplate('bad_reset_user.twig');
      
            return $template->render(array());
        }

        if (!$user->checkResetPasswordCode($req->get('reset_code'))) {
            $template = $app['twig']->loadTemplate('bad_reset_code.twig');
       
            return $template->render(array());
        }

        // Build password form and display it to the user
        $form_options = array(
            'user_id' => $req->get('user_id'),
            'reset_code' => $req->get('reset_code')
        );
        $form = $app['form.factory']->create(new \OpenCFP\Form\ResetForm(), $form_options);
        $template = $app['twig']->loadTemplate('reset_password.twig');
        
        return $template->render(array('form' => $form->createView()));
    }

    public function processResetAction(Request $req, Application $app)
    {
        $user_id = $req->get('user_id');
        $reset_code = $req->get('reset_code');
        $form_options = array(
            'user_id' => $user_id,
            'reset_code' => $reset_code
        );
        $form = $app['form.factory']->create(new \OpenCFP\Form\ResetForm(), $form_options);
        $form->bind($req);

        if (!$form->isValid()) {
            $template = $app['twig']->loadTemplate('reset_password.twig');

            return $template->render(array('form' => $form->createView()));
        }

        $data = $form->getData();
        
        // Make sure the user exists in the system
        try {
            $user = $app['sentry']->getUserProvider()->findById($data['user_id']);
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            $template = $app['twig']->loadTemplate('bad_reset_user.twig');
      
            return $template->render(array());
        }

        // Make sure they are using a valid code
        $response = $user->checkResetPasswordCode($data['reset_code']);

        if ($user->checkResetPasswordCode($data['reset_code']) !== true) {
            $template = $app['twig']->loadTemplate('bad_reset_code.twig');
            return $template->render(array());
        }

        /**
         * Can't let people replace their passwords with one they have
         * already
         */
        if ($user->checkPassword($data['password']) === true) {
            $passwordError = new \Symfony\Component\Form\FormError('text', 'Please select a different password than your current one.');
            $form->addError($passwordError);
            $template = $app['twig']->loadTemplate('reset_password.twig');

            return $template->render(array('form' => $form->createView()));
        }

        //Everything looks good, let's actually reset their password
        $template_name = 'reset_failure.twig';

        if ($user->attemptResetPassword($data['reset_code'], $data['password'])) {
            $template_name = 'reset_success.twig';
        }

        $template = $app['twig']-> loadTemplate($template_name);

        return $template->render(array());
    }

    protected function sendResetEmail($twig, $user_id, $email, $reset_code)
    {
        // Create our Mailer object
        $loader = new ConfigINIFileLoader(APP_DIR . '/config/config.ini');
        $config_data = $loader->load();
        $transport = new \Swift_SmtpTransport(
            $config_data['smtp']['host'],
            $config_data['smtp']['port']
        );
        
        if (!empty($config_data['smtp']['user'])) {
            $transport->setUsername($config_data['smtp']['user'])
                      ->setPassword($config_data['smtp']['password']);
        }

        if (!empty($config_data['smtp']['encryption'])) {
            $transport->setEncryption($config_data['smtp']['encryption']);
        }

        $mailer = new \Swift_Mailer($transport);
        $message = new \Swift_Message();

        // Build our email that we will send
        $template = $twig->loadTemplate('reset_password_email.twig');
        $parameters = array(
            'reset_code' => $reset_code,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST'])
            ? $_SERVER['HTTP_HOST'] : 'localhost',
            'user_id' => $user_id
        );
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
    }
}

