<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController
{
    public function indexAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');

        return $template->render(array());
    }

    public function processAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');
        $template_data = array();
        $code = 200;

        try {
            $page = new \OpenCFP\Login($app['sentry']);

            if ($page->authenticate($req->get('email'), $req->get('passwd'))) {
                return $app->redirect($app['url'] . '/dashboard');
            }

            $template_data = array(
                'user' => $app['sentry']->getUser(),
                'email' => $req->get('email'),
                'errorMessage' => $page->getAuthenticationMessage()
            );
            $code = 400;
        } catch (Exception $e) {
            $template_data = array(
                'user' => $app['sentry']->getUser(),
                'email' => $req->get('email'),
                'errorMessage' => $e->getMessage()
            );
            $code = 400;
        }

        return new Response($template->render($template_data), $code);
    }

    public function outAction(Request $req, Application $app)
    {
        $app['sentry']->logout();

        return $app->redirect($app['url'] . '/');
    }
}
