<?php

namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SecurityController
{
    /**
     * Displays the login form.
     *
     * @param Application $app
     * @return string
     */
    public function loginAction(Application $app)
    {
        return $app['twig']->render('login.twig');
    }

    /**
     * Authenticates the user.
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signinAction(Request $request, Application $app)
    {
        // Get submitted user's credentials
        $email = $request->request->get('email');
        $password = $request->request->get('passwd');

        // Try to authenticate the user
        $token = $app['security']->authenticate($email, $password);

        // Redirect the user if he's authenticated
        if ($token->isAuthenticated()) {
            return $app->redirect('/dashboard');
        }

        return $app['twig']->render('login.twig', array(
            'email' => $email,
            'error' => $token->getAuthenticationError(),
        ));
    }

    /**
     * Disconnects the user from his personal area.
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction(Application $app)
    {
        $app['sentry']->logout();

        return $app->redirect('/');
    }
}
