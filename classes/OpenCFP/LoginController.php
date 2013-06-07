<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    public function indexAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');

        return $template->render(array());
    }

    public function processAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');
        $templateData = array();

        try {
            $page = new \OpenCFP\Login($app['sentry']);

            if ($page->authenticate($req->get('email'), $req->get('passwd'))) {
                return $app->redirect('/dashboard');
            }
        } catch (Exception $e) {
            $templateData = array(
                'user' => $app['sentry']->getUser(),
                'errorMessage' => $e->getMessage(),
                'email' => $req->get('email')
            );
        }
        
        return $template->render($templateData);
    }
}
