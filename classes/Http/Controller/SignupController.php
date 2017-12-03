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

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class SignupController extends BaseController
{
    public function indexAction()
    {
        $auth = $this->service(Authentication::class);

        if ($auth->check()) {
            return $this->redirectTo('dashboard');
        }

        $cfp = $this->service(CallForPapers::class);

        if (!$cfp->isOpen()) {
            $this->service('session')->set('flash', [
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
            $this->validate([
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

            // Automatically authenticate the newly created user.
            $this->service(Authentication::class)->authenticate($request->get('email'), $request->get('password'));

            return $this->redirectTo('dashboard');
        } catch (ValidationException $e) {
            $this->app['session']->set('flash', [
                'type'  => 'error',
                'short' => $e->getMessage(),
                'ext'   => $e->errors(),
            ]);

            return $this->redirectBack();
        } catch (\RuntimeException $e) {
            $this->app['session']->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'A user already exists with that email address',
            ]);

            return $this->redirectBack();
        }
    }
}
