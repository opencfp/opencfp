<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SignupController
{
    public function indexAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('create_user.twig');
        return $template->render(array());
    }

    public function processAction(Request $req, Application $app)
    {
        $templateName = 'create_user.twig';
        $formData = array(
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'email' => $req->get('email'),
            'password' => $req->get('password'),
            'password2' => $req->get('password2')
        );
        $formData['speaker_info'] = $req->get('speaker_info') ?: null;
        $formData['speaker_bio'] = $req->get('speaker_bio') ?: null;

        $form = new \OpenCFP\SignupForm($formData);

        if ($form->validateAll()) {
            $sanitizedData = $form->sanitize();

            // Create account using Sentry
            $userData = array(
                'email' => $sanitizedData['email'],
                'password' => $sanitizedData['password'],
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
                    'info' => $sanitizedData['speaker_info'],
                    'bio' => $sanitizedData['speaker_bio']
                ));

                $templateName = 'create_user_success.twig';
                $formData['user'] = $user;
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
                'ext' => implode("<br>", $form->errorMessages)
            ));
        }
        
        $template = $app['twig']->loadTemplate($templateName);
        
        return $template->render($formData);
    }
}

