<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Cartalyst\Sentry\Users\UserExistsException;

class SignupController
{
    public function indexAction(Request $req, Application $app)
    {
        // Reset our user to make sure nothing weird happens
        if ($app['sentry']->check()) {
            $app['sentry']->logout();
        }

        $template = $app['twig']->loadTemplate('create_user.twig');
        $form_data = array();
        $form_data['formAction'] = '/signup';
        $form_data['buttonInfo'] = 'Create my speaker profile';

        return $template->render($form_data);
    }

    public function successAction(Application $app)
    {
        $template = $app['twig']->loadTemplate('create_user_success.twig');

        return $template->render(array());
    }

    public function processAction(Request $req, Application $app)
    {
        $template_name = 'create_user.twig';
        $form_data = array(
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'email' => $req->get('email'),
            'password' => $req->get('password'),
            'password2' => $req->get('password2')
        );
        $form_data['speaker_info'] = $req->get('speaker_info') ?: null;
        $form_data['speaker_bio'] = $req->get('speaker_bio') ?: null;

        $form = new \OpenCFP\SignupForm($form_data, $app['purifier']);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitized_data = $form->getSanitizedData();

            // Create account using Sentry
            $user_data = array(
                'first_name' => $sanitized_data['first_name'],
                'last_name' => $sanitized_data['last_name'],
                'email' => $sanitized_data['email'],
                'password' => $sanitized_data['password'],
                'activated' => 1
            );

            try {
                $user = $app['sentry']->getUserProvider()->create($user_data);

                // Add them to the proper group
                $adminGroup = $app['sentry']->getGroupProvider()->findByName('Speakers');
                $user->addGroup($adminGroup);

                // Create a Speaker record
                $speaker = new \OpenCFP\Speaker($app['db']);
                $response = $speaker->create(array(
                    'user_id' => $user->getId(),
                    'info' => $sanitized_data['speaker_info'],
                    'bio' => $sanitized_data['speaker_bio']
                ));

                return $app->redirect('/signup/success');
            } catch (UserExistsException $e) {
                $form_data['error_message'] = 'A user already exists with that email address';
            }
        } else {
            $form_data['error_message'] = implode("<br>", $form->error_messages);
        }

        $template = $app['twig']->loadTemplate('create_user.twig');
        $form_data['formAction'] = '/signup';
        $form_data['buttonInfo'] = 'Create my speaker profile';

        return $template->render($form_data);
    }
}
