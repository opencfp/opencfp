<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Login;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends BaseController
{
    use FlashableTrait;

    public function indexAction()
    {
        return $this->render('security/login.twig');
    }

    public function processAction(Request $req, Application $app)
    {
        /** @var Authentication $auth */
        $auth = $this->service(Authentication::class);

        try {
            $auth->authenticate($req->get('email'), $req->get('password'));

            // This is for redirecting to OAuth endpoint if we arrived
            // as part of the Authorization Code Grant flow.
            if ($this->service('session')->has('redirectTo')) {
                return new RedirectResponse($this->service('session')->get('redirectTo'));
            }

            return $this->redirectTo('dashboard');
        } catch (\Exception $e) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => $e->getMessage(),
            ]);

            $template_data = [
                'email' => $req->get('email'),
                'flash' => $this->getFlash($app),
            ];

            return $this->render('security/login.twig', $template_data, Response::HTTP_BAD_REQUEST);
        }
    }

    public function outAction()
    {
        $this->service(Authentication::class)->logout();

        return $this->redirectTo('homepage');
    }
}
