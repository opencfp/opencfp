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

final class ResetProcessAction
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

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $userId    = $request->get('user_id');
        $resetCode = $request->get('reset_code');

        if (empty($resetCode)) {
            throw new \Exception();
        }

        $this->resetForm->handleRequest($request);

        if (!$this->resetForm->isSubmitted() || !$this->resetForm->isValid()) {
            $this->resetForm->get('user_id')->setData($userId);
            $this->resetForm->get('reset_code')->setData($resetCode);

            $content = $this->twig->render('user/reset_password.twig', [
                'form' => $this->resetForm->createView(),
            ]);

            return new HttpFoundation\Response($content);
        }

        try {
            $user = $this->accountManagement->findById((int) $userId);
        } catch (\RuntimeException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'The reset you have requested appears to be invalid, please try again.',
            ]);

            $url = $this->urlGenerator->generate('forgot_password');

            return new HttpFoundation\RedirectResponse($url);
        }

        if (!$user->checkResetPasswordCode($resetCode)) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'The reset you have requested appears to be invalid, please try again.',
            ]);

            $url = $this->urlGenerator->generate('forgot_password');

            return new HttpFoundation\RedirectResponse($url);
        }

        $url = $this->urlGenerator->generate('forgot_password');

        return new HttpFoundation\RedirectResponse($url);
    }
}
