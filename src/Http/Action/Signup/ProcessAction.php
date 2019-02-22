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

namespace OpenCFP\Http\Action\Signup;

use OpenCFP\Domain\Services;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Infrastructure\Validation\RequestValidator;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class ProcessAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var Services\AccountManagement
     */
    private $accounts;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    public function __construct(
        Services\Authentication $authentication,
        Services\AccountManagement $accounts,
        RequestValidator $requestValidator,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication   = $authentication;
        $this->accounts         = $accounts;
        $this->requestValidator = $requestValidator;
        $this->urlGenerator     = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $this->requestValidator->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
                'coc'      => 'accepted',
                'privacy'  => 'accepted',
            ]);

            $this->accounts->create(
                $request->get('email'),
                $request->get('password'),
                [
                    'activated' => 1,
                ]
            );

            $this->accounts->activate($request->get('email'));

            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => "You've successfully created your account!",
            ]);

            $this->authentication->authenticate(
                $request->get('email'),
                $request->get('password')
            );

            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        } catch (ValidationException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => $e->getMessage(),
                'ext'   => $e->errors(),
            ]);

            return new HttpFoundation\RedirectResponse($request->headers->get('referer'));
        } catch (\RuntimeException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'A user already exists with that email address',
            ]);

            return new HttpFoundation\RedirectResponse($request->headers->get('referer'));
        }
    }
}
