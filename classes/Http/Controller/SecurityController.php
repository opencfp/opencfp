<?php

namespace OpenCFP\Http\Controller;

use Cartalyst\Sentry\Sentry;
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
        return $this->render('login.twig');
    }

    public function processAction(Request $req, Application $app)
    {
        try {
            /* @var Sentry $sentry */
            $sentry = $app['sentry'];

            // TODO Implement AuthenticationService
            $page = new Login($sentry);

            if ($page->authenticate($req->get('email'), $req->get('password'))) {
                // This is for redirecting to OAuth endpoint if we arrived
                // as part of the Authorization Code Grant flow.
                if ($this->service('session')->has('redirectTo')) {
                    return new RedirectResponse($this->service('session')->get('redirectTo'));
                }

                return $this->redirectTo('dashboard');
            }

            $errorMessage = $page->getAuthenticationMessage();

            $template_data = [
                'email' => $req->get('email'),
            ];
            $code = Response::HTTP_BAD_REQUEST;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $template_data = [
                'email' => $req->get('email'),
            ];
            $code = Response::HTTP_BAD_REQUEST;
        }

        // Set Success Flash Message
        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => $errorMessage,
        ]);

        $template_data['flash'] = $this->getFlash($app);

        return $this->render('login.twig', $template_data, $code);
    }

    public function outAction()
    {
        /* @var Sentry $sentry */
        $sentry = $this->service('sentry');

        // TODO Use AuthenticationService
        $sentry->logout();

        return $this->redirectTo('homepage');
    }
}
