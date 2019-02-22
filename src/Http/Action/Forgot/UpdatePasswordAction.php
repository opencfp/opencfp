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

namespace OpenCFP\Http\Action\Forgot;

use OpenCFP\Domain\Services;
use Symfony\Component\Form;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class UpdatePasswordAction
{
    /**
     * @var Form\FormInterface
     */
    private $resetForm;

    /**
     * @var Services\AccountManagement
     */
    private $accountManagement;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Form\FormInterface $resetForm,
        Services\AccountManagement $accountManagement,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->resetForm         = $resetForm;
        $this->accountManagement = $accountManagement;
        $this->twig              = $twig;
        $this->urlGenerator      = $urlGenerator;
    }

    /**
     * @param HttpFoundation\Request $request
     *
     * @throws \Exception
     *
     * @return HttpFoundation\Response
     */
    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $this->resetForm->handleRequest($request);

        if (!$this->resetForm->isSubmitted() || !$this->resetForm->isValid()) {
            $content = $this->twig->render('user/reset_password.twig', [
                'form' => $this->resetForm->createView(),
            ]);

            return new HttpFoundation\Response($content);
        }

        $data = $this->resetForm->getData();

        // We cast user_id to always be an integer to satisfy Sentinel findById() requirements
        $userId    = (int) $data['user_id'];
        $resetCode = $data['reset_code'];
        $password  = $data['password'];

        if (empty($resetCode)) {
            throw new \Exception();
        }

        try {
            $user = $this->accountManagement->findById($userId);
        } catch (\RuntimeException $e) {
            echo $e;
            die();
        }

        if ($user->checkPassword($password)) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Please select a different password than your current one.',
            ]);

            $url = $this->urlGenerator->generate('login');

            return new HttpFoundation\RedirectResponse($url);
        }

        if (!$user->attemptResetPassword($resetCode, $password)) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Password reset failed, please contact the administrator.',
            ]);

            $url = $this->urlGenerator->generate('homepage');

            return new HttpFoundation\RedirectResponse($url);
        }

        $request->getSession()->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => "You've successfully reset your password.",
        ]);

        $url = $this->urlGenerator->generate('login');

        return new HttpFoundation\RedirectResponse($url);
    }
}
