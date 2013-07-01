<?php

namespace OpenCFP;

use Silex\Application;
use OpenCFP\Speaker;
use OpenCFP\SignupForm;
use Cartalyst\Sentry\Users\UserExistsException;
use Symfony\Component\HttpFoundation\Request;

class SignupController
{
    public function indexAction(Application $app)
    {
        // Reset our user to make sure nothing weird happens
        if ($app['sentry']->check()) {
            $app['sentry']->logout();
        }

        return $app['twig']->render('create_user.twig');
    }

    public function successAction(Application $app)
    {
        return $app['twig']->render('create_user_success.twig');
    }

    public function processAction(Request $request, Application $app)
    {
        $form = new SignupForm($app['purifier'], array('action' => 'create'));
        $form->submit($request->request->get('user'));

        if ($form->isValid()) {
            // @todo move to a registration service
            // @todo use a transaction to execute multiple insert
            try {
                $user = $app['sentry']->getUserProvider()->create($form->getUserData());

                // Add them to the proper group
                $adminGroup = $app['sentry']->getGroupProvider()->findByName('Speakers');
                $user->addGroup($adminGroup);

                // Create a Speaker record
                $data = $form->getProfileData(array('user_id' => $user->getId()));
                $speaker = new Speaker($app['db']);
                $speaker->create($data);

                return $app->redirect('/signup/success');
            } catch (UserExistsException $e) {
                $form_data['error_message'] = 'A user already exists with that email address';
            } catch (\Exception $e) {
                $app['session']->getFlashBag()->set('error', $e->getMessage());
                $form_data['error_message'] = $e->getMessage();
            }
        } else {
            $form_data['error_message'] = implode("<br>", $form->getErrorMessages());
        }

        return $app['twig']->render('create_user.twig', $form_data);
    }
}
