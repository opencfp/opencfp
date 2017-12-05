<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\ResetEmailer;
use OpenCFP\Http\Form\ForgotForm;
use OpenCFP\Http\Form\ResetForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;

class ForgotController extends BaseController
{
    public function indexAction()
    {
        /** @var FormFactory $formFactory */
        $formFactory = $this->service('form.factory');

        $form = $formFactory->createBuilder(ForgotForm::class)->getForm();

        $data = [
            'form'         => $form->createView(),
            'current_page' => 'Forgot Password',
        ];

        return $this->render('security/forgot_password.twig', $data);
    }

    public function sendResetAction(Request $request)
    {
        /** @var FormFactory $formFactory */
        $formFactory = $this->service('form.factory');

        $form = $formFactory
            ->createBuilder(ForgotForm::class)
            ->getForm();

        $form->handleRequest($request);

        /** @var Session\Session $session */
        $session = $this->service('session');

        if (!$form->isValid()) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Please enter a properly formatted email address',
            ]);

            return $this->redirectTo('forgot_password');
        }

        // Check to make sure they actually exist in the system...
        $data = $form->getData();

        try {
            /** @var AccountManagement $accounts */
            $accounts = $this->service(AccountManagement::class);

            $user = $accounts->findByLogin($data['email']);
        } catch (\RuntimeException $e) {
            $session->set('flash', $this->successfulSendFlashParameters($data['email']));

            return $this->redirectTo('forgot_password');
        }

        // Create a reset code and email the URL to our user

        /** @var ResetEmailer $resetEmailer */
        $resetEmailer = $this->service('reset_emailer');

        $response = $resetEmailer->send($user->getId(), $data['email'], $user->getResetPasswordCode());

        if ($response == false) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to send your password reset request. Please try again',
            ]);

            return $this->redirectTo('forgot_password');
        }

        $session->set('flash', $this->successfulSendFlashParameters($data['email']));

        return $this->redirectTo('login');
    }

    public function resetAction(Request $request)
    {
        if (empty($request->get('reset_code'))) {
            throw new \Exception();
        }

        $errorMessage = 'The reset you have requested appears to be invalid, please try again.';
        $error        = 0;

        try {
            /** @var AccountManagement $accounts */
            $accounts = $this->service(AccountManagement::class);

            $user = $accounts->findById($request->get('user_id'));

            if (!$user->checkResetPasswordCode($request->get('reset_code'))) {
                ++$error;
            }
        } catch (\RuntimeException $e) {
            ++$error;
        }

        if ($error > 0) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => $errorMessage,
            ]);
        }
        
        // Build password form and display it to the user
        $formOptions = [
            'user_id'    => $request->get('user_id'),
            'reset_code' => $request->get('reset_code'),
        ];

        /** @var FormFactory $formFactory */
        $formFactory = $this->service('form.factory');

        $form = $formFactory->create(new ResetForm());

        $data['form']  = $form->createView($formOptions);
        $data['flash'] = $this->app['session']->get('flash');

        return $this->render('user/forgot_password.twig', $data);
    }

    public function processResetAction(Request $request)
    {
        $userId    = $request->get('user_id');
        $resetCode = $request->get('reset_code');

        if (empty($resetCode)) {
            throw new \Exception();
        }

        /** @var FormFactory $formFactory */
        $formFactory = $this->service('form.factory');

        $form = $formFactory->createBuilder(ResetForm::class)->getForm();

        $form->handleRequest($request);
        
        if (!$form->isValid()) {
            $form->get('user_id')->setData($userId);
            $form->get('reset_code')->setData($resetCode);

            return $this->render('user/reset_password.twig', ['form' => $form->createView()]);
        }
             
        $errorMessage = 'The reset you have requested appears to be invalid, please try again.';
        $error        = 0;

        try {
            /** @var AccountManagement $accounts */
            $accounts = $this->service(AccountManagement::class);

            $user = $accounts->findById($request->get('user_id'));
        } catch (\RuntimeException $e) {
            ++$error;
        }

        if (!$user->checkResetPasswordCode($request->get('reset_code'))) {
            ++$error;
        }

        if ($error > 0) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => $errorMessage,
            ]);
        }

        return $this->redirectTo('forgot_password');
    }

    public function updatePasswordAction(Request $request)
    {
        /** @var FormFactory $formFactory */
        $formFactory = $this->service('form.factory');

        $form = $formFactory->createBuilder(ResetForm::class)->getForm();

        $form->handleRequest($request);
        
        if (!$form->isValid()) {
            return $this->render('user/reset_password.twig', ['form' => $form->createView()]);
        }

        $data      = $form->getData();
        $userId    = $data['user_id'];
        $resetCode = $data['reset_code'];
        $password  = $data['password'];

        if (empty($resetCode)) {
            throw new \Exception();
        }

        try {
            /** @var AccountManagement $accounts */
            $accounts = $this->service(AccountManagement::class);

            $user = $accounts->findById($userId);
        } catch (\RuntimeException $e) {
            echo $e;
            die();
        }

        /** @var Session\Session $session */
        $session = $this->service('session');

        /**
         * Can't let people replace their passwords with one they have
         * already
         */
        if ($user->checkPassword($password) === true) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Please select a different password than your current one.',
            ]);

            return $this->redirectTo('login');
        }

        // Everything looks good, let's actually reset their password
        if ($user->attemptResetPassword($resetCode, $password)) {
            $session->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => "You've successfully reset your password.",
            ]);

            return $this->redirectTo('login');
        }

        // user may have tried using the recovery twice
        $session->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => 'Password reset failed, please contact the administrator.',
        ]);

        return $this->redirectTo('homepage');
    }

    protected function successfulSendFlashParameters($email)
    {
        return [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => "If your email was valid, we sent a link to reset your password to $email",
        ];
    }
}
