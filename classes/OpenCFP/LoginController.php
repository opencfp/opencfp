<?php

namespace OpenCFP;

use Silex\Application;
use OpenCFP\Login;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    public function indexAction(Application $app)
    {
        return $app['twig']->render('login.twig');
    }

    public function processAction(Request $request, Application $app)
    {
        $login = new Login($app['sentry']);

        $email = $request->request->get('email');
        if ($login->authenticate($email, $request->request->get('passwd'))) {
            return $app->redirect('/dashboard');
        }

        return $app['twig']->render('login.twig', array(
            'email' => $email,
            'error' => $login->getAuthenticationMessage(),
        ));
    }

    public function outAction(Application $app)
    {
        $app['sentry']->logout();

        return $app->redirect('/');
    }
}
