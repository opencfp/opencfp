<?php

namespace OpenCFP\Controller;

use Silex\Application;
use OpenCFP\Speaker;
use OpenCFP\SignupForm;
use Symfony\Component\HttpFoundation\Request;

class ProfileController
{
    public function editAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $profile = $app['profile']->getSpeakerProfile();
        $form = $app['profile']->createProfileForm($profile);

        return $app['twig']->render('edit_user.twig', array('form' => $form));
    }

    public function updateAction(Request $request, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $profile = $app['profile']->getSpeakerProfile();
        $form = $app['profile']->createProfileForm($profile);
        $form->submit($request->request->get('user'));

        if ($form->isValid()) {
            $app['profile']->updateProfile($form);
            $app['session']->getFlashBag()->set('success', 'Successfully updated your information!');

            return $app->redirect('/profile/edit');
        }

        return $app['twig']->render('edit_user.twig', array('form' => $form));
    }

    public function passwordAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        return $app['twig']->render('change_password.twig', array(
            'form' => $app['change_password']->createChangePasswordForm(),
        ));
    }

    public function passwordProcessAction(Request $request, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $form = $app['change_password']->createChangePasswordForm();
        $form->submit($request->request->get('password'));

        if ($form->isValid()) {
            $app['change_password']->changeUserPassword($form);
            $app['session']->getFlashBag()->set('success', 'Changed your password.');

            return $app->redirect('/profile/change_password');
        }

        return $app['twig']->render('change_password.twig', array('form' => $form));
    }
}

