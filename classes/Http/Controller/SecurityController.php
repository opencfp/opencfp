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
use OpenCFP\Domain\Services\Authentication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session;

class SecurityController extends BaseController
{
    use ContainerAware;

    public function indexAction()
    {
        return $this->render('security/login.twig', [
            'email' => null,
        ]);
    }

    public function processAction(Request $request)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        try {
            $auth->authenticate($request->get('email'), $request->get('password'));

            return $this->redirectTo('dashboard');
        } catch (\Exception $e) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => $e->getMessage(),
            ]);

            $templateData = [
                'email' => $request->get('email'),
                'flash' => $this->app['session']->get('flash'),
            ];

            return $this->render('security/login.twig', $templateData, Response::HTTP_BAD_REQUEST);
        }
    }

    public function outAction()
    {
        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        $authentication->logout();

        return $this->redirectTo('homepage');
    }
}
