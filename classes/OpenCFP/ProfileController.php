<?php
namespace OpenCFP;

use Silex\Application;
use OpenCFP\Speaker;
use OpenCFP\SignupForm;
use Symfony\Component\HttpFoundation\Request;

class ProfileController
{
    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();
        if ($user->getId() !== $req->get('id')) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "You cannot edit someone else's profile"
            ));
            return $app->redirect('/dashboard');
        }

        $speaker = new Speaker($app['db']);
        $speaker_data = $speaker->getDetailsByUserId($user->getId());
        $form_data = array(
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'id' => $user->getId(),
            'user' => $user
        );

        return $app['twig']->render('edit_user.twig', $form_data);
    }

    public function processAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "You cannot edit someone else's profile"
            ));
            return $app->redirect('/dashboard');
        }

        $form_data = array(
            'email' => $req->get('email'),
            'user_id' => $req->get('id'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
        );
        $form_data['speaker_info'] = $req->get('speaker_info') ?: null;
        $form_data['speaker_bio'] = $req->get('speaker_bio') ?: null;

        $form = new SignupForm($form_data, $app['purifier']);

        if ($form->validateAll('update') == true) {
            $sanitized_data = $form->sanitize();
            $speaker = new Speaker($app['db']);
            if (!$speaker->update($form_data)) {
                $app['session']->getFlashBag()->set('flash', array(
                    'type' => 'error',
                    'short' => 'Error!',
                    'ext' => "We were unable to update the speaker information"
                ));
            } else {
                $app['session']->getFlashBag()->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Updated your profile'
                ));
            }
        }

        $form_data['id'] = $user->getId();
        $form_data['user'] = $user;

        return $app['twig']->render('edit_user.twig', $form_data);
    }

    public function passwordAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        return $app['twig']->render('change_password.twig', array(
            'user' => $app['sentry']->getUser(),
        ));
    }

    public function passwordProcessAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();

        /**
         * Okay, the logic is kind of weird but we can use the SignupFOrm
         * validation code to make sure our password changes are good
         */
        $formData = array(
            'password' => $req->get('passwd'),
            'password2' => $req->get('passwd_confirm')
        );
        $form = new SignupForm($formData, $app['purifier']);

        if ($form->validatePasswords() === false) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => 'Error!',
                'ext' => implode("<br>", $form->error_messages)
            ));
            return $app->redirect('/profile/change_password');
        }

        $sanitized_data = $form->sanitize();
        $speaker = new Speaker($app['db']);

        if ($speaker->changePassword($sanitized_data['password'], $user) === false) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => 'Error!',
                'ext' => "Unable to update your password in the database. Please try again."
            ));
            return $app->redirect('/profile/change_password');
        }

        $app['session']->getFlashBag()->set('flash', array(
            'type' => 'success',
            'short' => 'Success!',
            'ext' => "Changed your password."
        ));

        return $app->redirect('/profile/change_password');
    }
}

