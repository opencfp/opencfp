<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ProfileController
{
    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $template = $app['twig']->loadTemplate('edit_user.twig');
        $user = $app['sentry']->getUser();
        
        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "You cannot edit someone else's profile"
            ));
            return $app->redirect('/dashboard');
        }

        $speaker = new \OpenCFP\Speaker($app['db']);
        $speaker_data = $speaker->getDetailsByUserId($user->getId());
        $form_data = array(
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'id' => $user->getId(),
            'formAction' => '/profile/edit',
            'buttonInfo' => 'Update Profile',
            'user' => $user
        );

        return $template->render($form_data) ;
    }

    public function processAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }
        
        $user = $app['sentry']->getUser();

        if ($user->getId() !== $req->get('id')) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => '',
                'ext' => "You cannot edit someone else's profile"
            ));
            die('trying to edit a profile that is not yours');
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

        $form = new \OpenCFP\SignupForm($form_data, $app['purifier']);

        if ($form->validateAll('update') == true) {
            $sanitized_data = $form->sanitize();
            $speaker = new \OpenCFP\Speaker($app['db']);
            $response = $speaker->update($form_data);
            $template_name = 'edit_user.twig';

            if ($response == false) {
                $app['session']->set('flash', array(
                    'type' => 'error',
                    'short' => 'Error!',
                    'ext' => "We were unable to update the speaker information"
                ));
            }

            if ($response == true) {
                $app['session']->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Updated your profile' 
                ));
            }
        } 

        $form_data['buttonInfo'] = 'Update Profile';
        $form_data['id'] = $user->getId();
        $form_data['user'] = $user;
        $template = $app['twig']->loadTemplate($template_name);
        
        return $template->render($form_data);
    }

    public function passwordAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }
        $user = $app['sentry']->getUser();
         
        $template = $app['twig']->loadTemplate('change_password.twig');

        return $template->render(array('user' => $user));        
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
        $form = new \OpenCFP\SignupForm($formData, $app['purifier']);

        if ($form->validatePasswords() === false) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error!',
                'ext' => implode("<br>", $form->error_messages)
            ));
            return $app->redirect('/profile/change_password');
        }

        $sanitized_data = $form->sanitize();
        $speaker = new \OpenCFP\Speaker($app['db']);

        if ($speaker->changePassword($sanitized_data['password'], $user) === false) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error!',
                'ext' => "Unable to update your password in the database. Please try again."
            ));
            return $app->redirect('/profile/change_password');
        }
        
        $app['session']->set('flash', array(
            'type' => 'success',
            'short' => 'Success!',
            'ext' => "Changed your password."
        ));

        return $app->redirect('/profile/change_password');

    }
}

