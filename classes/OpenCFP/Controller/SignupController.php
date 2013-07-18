<?php

namespace OpenCFP\Controller;

use Silex\Application;
use OpenCFP\Form\SignupForm;
use Symfony\Component\HttpFoundation\Request;

class SignupController
{
    /**
     * Renders the registration form.
     *
     * @param Application $app The service container
     * @return string The response content
     */
    public function indexAction(Application $app)
    {
        // Reset our user to make sure nothing weird happens
        if ($app['sentry']->check()) {
            $app['sentry']->logout();
        }

        return $app['twig']->render('create_user.twig', array(
            'form' => $app['registration']->createSignupForm(array('action' => 'create')),
        ));
    }

    /**
     * Processes the registration form.
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function processAction(Request $request, Application $app)
    {
        $form = $app['registration']->createSignupForm(array('action' => 'create'));
        $form->submit($request->request->get('user'));

        if ($form->isValid()) {
            if ($app['registration']->createUserAccount($form)) {
                return $app->redirect('/signup/success');
            }
        }

        return $app['twig']->render('create_user.twig', array('form' => $form));
    }

    /**
     * Confirms the creation of the user account.
     *
     * @param Application $app
     * @return string
     */
    public function successAction(Application $app)
    {
        return $app['twig']->render('create_user_success.twig');
    }
}
