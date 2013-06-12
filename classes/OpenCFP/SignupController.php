<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SignupController
{
    public function indexAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('create_user.twig');
        return $template->render(array('formAction' => '/signup'));
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

        $form = new \OpenCFP\SignupForm($form_data);

        if ($form->validateAll()) {
            $sanitized_data = $form->sanitize();

            // Create account using Sentry
            $userData = array(
                'first_name' => $sanitized_data['first_name'],
                'last_name' => $sanitized_data['last_name'],
                'email' => $sanitized_data['email'],
                'password' => $sanitized_data['password'],
                'activated' => 1
            );

            try {
                $user = $app['sentry']->getUserProvider()->create($userData);

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

                $template_name = 'create_user_success.twig';
                $form_data['user'] = $user;
            } catch (Cartalyst\Sentry\Users\UserExistsException $e) {
                $app['session']->set('flash', array(
                    'type' => 'error',
                    'short' => 'Error!',
                    'ext' => 'A user already exists with that email address'
                ));
            }
        }

        if (!$form->validateAll()) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error!',
                'ext' => implode("<br>", $form->error_messages)
            ));
        }
        
        $template = $app['twig']->loadTemplate($template_name);
        
        return $template->render($form_data);
    }
}

