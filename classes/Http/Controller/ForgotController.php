<?php

namespace OpenCFP\Http\Controller;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Exception;
use OpenCFP\Http\Form\ForgotForm;
use OpenCFP\Http\Form\ResetForm;
use Symfony\Component\HttpFoundation\Request;

class ForgotController extends BaseController
{
    use FlashableTrait;

    public function indexAction()
    {
        $form = $this->service('form.factory')->createBuilder(ForgotForm::class)->getForm();
        $data = [
            'form' => $form->createView(),
            'current_page' => 'Forgot Password',
        ];
        return $this->render('user/forgot_password.twig', $data);
    }

    public function sendResetAction(Request $req)
    {
        $form = $this->service('form.factory')
            ->createBuilder(ForgotForm::class)
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Please enter a properly formatted email address",
            ]);

            return $this->redirectTo('forgot_password');
        }

        // Check to make sure they actually exist in the system...
        $data = $form->getData();

        try {
            /* @var Sentry $sentry */
            $sentry = $this->service('sentry');
            $user = $sentry->getUserProvider()->findByLogin($data['email']);
        } catch (UserNotFoundException $e) {
            $this->service('session')->set('flash', $this->successfulSendFlashParameters($data['email']));
            return $this->redirectTo('forgot_password');
        }

        // Create a reset code and email the URL to our user
        $response = $this->service('reset_emailer')->send($user->getId(), $data['email'], $user->getResetPasswordCode());

        if ($response == false) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "We were unable to send your password reset request. Please try again",
            ]);

            return $this->redirectTo('forgot_password');
        }

        $this->service('session')->set('flash', $this->successfulSendFlashParameters($data['email']));

        return $this->redirectTo('login');
    }

    public function resetAction(Request $req)
    {
        if (empty($req->get('reset_code'))) {
            throw new Exception();
        }

        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;
        try {
            /* @var Sentry $sentry */
            $sentry = $this->service('sentry');

            $user = $sentry->getUserProvider()->findById($req->get('user_id'));

            if (!$user->checkResetPasswordCode($req->get('reset_code'))) {
                $error++;
            }
        } catch (UserNotFoundException $e) {
            $error++;
        }

        if ($error > 0) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => $errorMessage,
            ]);
        }

        // Build password form and display it to the user
        $form_options = [
            'user_id' => $req->get('user_id'),
            'reset_code' => $req->get('reset_code'),
        ];
        $form = $this->service('form.factory')->create(new ResetForm(), $form_options);

        $data['form'] = $form->createView();
        $data['flash'] = $this->getFlash($this->app);

        return $this->render('user/forgot_password.twig', $data);
    }

    public function processResetAction(Request $req)
    {
        $user_id = $req->get('user_id');
        $reset_code = $req->get('reset_code');

        if (empty($reset_code)) {
            throw new Exception();
        }

        $form_options = [
            'user_id' => $user_id,
            'reset_code' => $reset_code,
        ];
        $form = $this->service('form.factory')->create(new ResetForm(), $form_options);

        if (! $form->isValid()) {
            return $this->render('user/reset_password.twig', ['form' => $form->createView()]);
        }

        $errorMessage = "The reset you have requested appears to be invalid, please try again.";
        $error = 0;

        try {
            /* @var Sentry $sentry */
            $sentry = $this->service('sentry');

            $user = $sentry->getUserProvider()->findById($req->get('user_id'));
        } catch (UserNotFoundException $e) {
            $error++;
        }

        if (! $user->checkResetPasswordCode($req->get('reset_code'))) {
            $error++;
        }

        if ($error > 0) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => $errorMessage,
            ]);
        }

        return $this->redirectTo('forgot_password');
    }

    public function updatePasswordAction(Request $req)
    {
        $postArray = $req->request->all();

        $user_id = $postArray['reset']['user_id'];
        $reset_code = $postArray['reset']['reset_code'];
        $password = $postArray['reset']['password']['password'];

        if (empty($reset_code)) {
            throw new Exception();
        }

        try {
            /* @var Sentry $sentry */
            $sentry = $this->service('sentry');

            $user = $sentry->getUserProvider()->findById($user_id);
        } catch (UserNotFoundException $e) {
            echo $e;
            die();
        }

        /**
         * Can't let people replace their passwords with one they have
         * already
         */
        if ($user->checkPassword($password) === true) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Please select a different password than your current one.",
            ]);

            return $this->redirectTo('login');
        }

        // Everything looks good, let's actually reset their password
        if ($user->attemptResetPassword($reset_code, $password)) {
            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => "You've successfully reset your password.",
            ]);

            return $this->redirectTo('login');
        }

        // user may have tried using the recovery twice
        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => "Password reset failed, please contact the administrator.",
        ]);

        return $this->redirectTo('homepage');
    }

    protected function successfulSendFlashParameters($email)
    {
        return [
            'type' => 'success',
            'short' => 'Success',
            'ext' => "If your email was valid, we sent a link to reset your password to $email",
        ];
    }
}
