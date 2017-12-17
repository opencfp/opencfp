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
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class SignupController extends BaseController
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var AccountManagement
     */
    private $accounts;

    public function __construct(
        Authentication $authentication,
        AccountManagement $accounts,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->accounts       = $accounts;

        parent::__construct($twig, $urlGenerator);
    }

    public function processAction(Request $request): Response
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
                'coc'      => 'accepted',
            ]);

            $this->accounts->create($request->get('email'), $request->get('password'), [
                'activated' => 1,
            ]);
            $this->accounts->activate($request->get('email'));

            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => "You've successfully created your account!",
            ]);

            // Automatically authenticate the newly created user.
            $this->authentication->authenticate($request->get('email'), $request->get('password'));

            return $this->redirectTo('dashboard');
        } catch (ValidationException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => $e->getMessage(),
                'ext'   => $e->errors(),
            ]);

            return $this->redirectBack($request);
        } catch (\RuntimeException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'A user already exists with that email address',
            ]);

            return $this->redirectBack($request);
        }
    }
}
