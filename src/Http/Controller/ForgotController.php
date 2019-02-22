<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\ResetEmailer;
use OpenCFP\Http\Form\ForgotFormType;
use OpenCFP\Http\Form\ResetFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class ForgotController extends BaseController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var AccountManagement
     */
    private $accounts;

    /**
     * @var ResetEmailer
     */
    private $resetEmailer;

    public function __construct(
        FormFactoryInterface $formFactory,
        AccountManagement $accounts,
        ResetEmailer $resetEmailer,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->formFactory  = $formFactory;
        $this->accounts     = $accounts;
        $this->resetEmailer = $resetEmailer;

        parent::__construct($twig, $urlGenerator);
    }

    public function sendResetAction(Request $request): Response
    {
        $form = $this->formFactory
            ->createBuilder(ForgotFormType::class)
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Please enter a properly formatted email address',
            ]);

            return $this->redirectTo('forgot_password');
        }
        // Check to make sure they actually exist in the system...
        $data = $form->getData();

        try {
            $user = $this->accounts->findByLogin($data['email']);
        } catch (\RuntimeException $e) {
            $request->getSession()->set('flash', $this->successfulSendFlashParameters($data['email']));

            return $this->redirectTo('forgot_password');
        }
        // Create a reset code and email the URL to our user
        $response = $this->resetEmailer->send($user->getId(), $data['email'], $user->getResetPasswordCode());

        if ($response === false) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to send your password reset request. Please try again',
            ]);

            return $this->redirectTo('forgot_password');
        }

        $request->getSession()->set('flash', $this->successfulSendFlashParameters($data['email']));

        return $this->redirectTo('login');
    }

    public function resetAction(Request $request): Response
    {
        if (empty($request->get('reset_code'))) {
            throw new \Exception();
        }

        $errorMessage = 'The reset you have requested appears to be invalid, please try again.';
        $error        = 0;

        try {
            $user = $this->accounts->findById($request->get('user_id'));

            if (!$user->checkResetPasswordCode($request->get('reset_code'))) {
                ++$error;
            }
        } catch (\RuntimeException $e) {
            ++$error;
        }

        if ($error > 0) {
            $request->getSession()->set('flash', [
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
        $form = $this->formFactory->create(new ResetFormType());

        return $this->render('user/forgot_password.twig', [
            'form'  => $form->createView($formOptions),
            'flash' => $request->getSession()->get('flash'),
        ]);
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
