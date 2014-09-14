<?php
namespace OpenCFP\Controller;

use OpenCFP\Config\ConfigINIFileLoader;
//use OpenCFP\Model\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ForgotController
{
    public function getFlash(Application $app)
    {
        $flash = $app['session']->get('flash');
        $this->clearFlash($app);
        return $flash;
    }

    public function clearFlash(Application $app)
    {
        $app['session']->set('flash', null);
    }

    public function indexAction(Request $req, Application $app)
    {
        $form = $app['form.factory']->create(new \OpenCFP\Form\ForgotForm());
        $template = $app['twig']->loadTemplate('user/forgot_password.twig');

        $data = array(
            'form' => $form->createView(),
            'current_page' => "Forgot Password"
        );

        return $template->render($data);
    }

    public function sendResetAction(Request $req, Application $app)
    {
        $form = $app['form.factory']->create(new \OpenCFP\Form\ForgotForm());
        $form->bind($req);

        if (!$form->isValid()) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Please enter a properly formatted email address"
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
                'short' => 'Error',
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
                'short' => 'Error',
                'ext' => "We were unable to send your password reset request. Please try again"
            ));

            return $app->redirect($app['url'] . '/forgot');
        }

        $app['session']->set('flash', array(
                'type' => 'success',
                'short' => 'Success',
                'ext' => "An email giving you a link to reset your password has been sent."
        ));

        return $app->redirect($app['url'] . '/login');
    }


    public function resetAction(Request $req, Application $app)
    {
        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;
        try {
            $user = $app['sentry']->getUserProvider()->findById($req->get('user_id'));

            if (!$user->checkResetPasswordCode($req->get('reset_code'))) {
                $error++;
            }
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            $error++;
        }

        if ($error > 0) {
            $app['session']->set('flash', array(
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
        $form = $app['form.factory']->create(new \OpenCFP\Form\ResetForm(), $form_options);
        $template = $app['twig']->loadTemplate('user/forgot_password.twig');

        $data['form'] = $form->createView();
        $data['flash'] = $this->getFlash($app);

        return $template->render($data);
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

        if (!$form->isValid()) {
            $template = $app['twig']->loadTemplate('user/reset_password.twig');

            return $template->render(array('form' => $form->createView()));
        }

//        $data = $form->getData();

        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;
        try {
            $user = $app['sentry']->getUserProvider()->findById($req->get('user_id'));
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            $error++;
        }

        if (!$user->checkResetPasswordCode($req->get('reset_code'))) {
            $error++;
        }

        if ($error > 0) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => $errorMessage,
            ));
        }

        return $app->redirect($app['url'] . '/forgot');
    }
    
    public function updatePasswordAction(Request $req, Application $app)
    {
        $postArray = $req->request->all();
        $user_id = $postArray['reset']['user_id'];
        $reset_code = $postArray['reset']['reset_code'];
        $password = $postArray['reset']['password']['password'];
        
        try {
            $user = $app['sentry']->getUserProvider()->findById($user_id);
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            echo $e;
            die();
        }
        
        /**
         * Can't let people replace their passwords with one they have
         * already
         */
        if ($user->checkPassword($password) === true) {
            $app['session']->set('flash', array(
                    'type' => 'error',
                    'short' => 'Error',
                    'ext' => "Please select a different password than your current one.",
                ));
            return $app->redirect($app['url'] . '/login');
        }

        // Everything looks good, let's actually reset their password
        if ($user->attemptResetPassword($reset_code, $password)) {
            $app['session']->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => "You've successfully reset your password.",
                ));
            return $app->redirect($app['url'] . '/login');
        }

        // user may have tried using the recovery twice
        $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Password reset failed, please contact the administrator.",
            ));
        return $app->redirect($app['url'] . '/');
    }

    protected function sendResetEmail($twig, $user_id, $email, $reset_code)
    {
        // Create our Mailer object
        $loader = new ConfigINIFileLoader(APP_DIR . '/config/config.' . APP_ENV . '.ini');
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

        // Build our email that we will send
        $template = $twig->loadTemplate('emails/reset_password.twig');
        $parameters = array(
            'reset_code' => $reset_code,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST'])
            ? $_SERVER['HTTP_HOST'] : 'localhost',
            'user_id' => $user_id,
            'email' => $config_data['application']['email'],
            'title' => $config_data['application']['title']
        );
        
        try {
            $mailer = new \Swift_Mailer($transport);
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
            echo $e;die();
        }
    }
}

