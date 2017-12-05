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

use OpenCFP\ContainerAware;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;

class SignupController extends BaseController
{
    use ContainerAware;

    public function indexAction()
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        if ($auth->isAuthenticated()) {
            return $this->redirectTo('dashboard');
        }

        /** @var CallForPapers $cfp */
        $cfp = $this->service(CallForPapers::class);

        if (!$cfp->isOpen()) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Sorry, the call for papers has ended.',
            ]);

            return $this->redirectTo('homepage');
        }

        return $this->render('security/signup.twig');
    }

    public function processAction(Request $request)
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
                'coc'      => 'accepted',
            ]);

            /** @var AccountManagement $accounts */
            $accounts = $this->service(AccountManagement::class);

            $user = $accounts->create($request->get('email'), $request->get('password'), [
                'activated' => 1,
            ]);
            $accounts->activate($request->get('email'));

            $this->app['session']->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => "You've successfully created your account!",
            ]);

            /** @var Authentication $authentication */
            $authentication = $this->service(Authentication::class);

            // Automatically authenticate the newly created user.
            $authentication->authenticate($request->get('email'), $request->get('password'));

            return $this->redirectTo('dashboard');
        } catch (ValidationException $e) {
            $this->app['session']->set('flash', [
                'type'  => 'error',
                'short' => $e->getMessage(),
                'ext'   => $e->errors(),
            ]);

            return $this->redirectBack($request);
        } catch (\RuntimeException $e) {
            $this->app['session']->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'A user already exists with that email address',
            ]);

            return $this->redirectBack($request);
        }
    }
}
