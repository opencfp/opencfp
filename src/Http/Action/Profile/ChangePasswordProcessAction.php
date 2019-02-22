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

namespace OpenCFP\Http\Action\Profile;

use HTMLPurifier;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Form;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class ChangePasswordProcessAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        HTMLPurifier $purifier,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->purifier       = $purifier;
        $this->urlGenerator   = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $user = $this->authentication->user();

        /**
         * Okay, the logic is kind of weird but we can use the SignupForm
         * validation code to make sure our password changes are good
         */
        $formData = [
            'password'  => $request->get('password'),
            'password2' => $request->get('password_confirm'),
        ];

        $form = new Form\SignupForm(
            $formData,
            $this->purifier
        );

        $form->sanitize();

        if (!$form->validatePasswords()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br>', $form->getErrorMessages()),
            ]);

            $url = $this->urlGenerator->generate('password_edit');

            return new HttpFoundation\RedirectResponse($url);
        }

        $resetCode     = $user->getResetPasswordCode();
        $sanitizedData = $form->getCleanData();

        if (!$user->attemptResetPassword($resetCode, $sanitizedData['password'])) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Unable to update your password in the database. Please try again.',
            ]);

            $url = $this->urlGenerator->generate('password_edit');

            return new HttpFoundation\RedirectResponse($url);
        }

        $request->getSession()->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => 'Changed your password.',
        ]);

        $url = $this->urlGenerator->generate('password_edit');

        return new HttpFoundation\RedirectResponse($url);
    }
}
