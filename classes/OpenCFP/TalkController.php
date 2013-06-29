<?php

namespace OpenCFP;

use Silex\Application;
use OpenCFP\TalkForm;
use OpenCFP\Talk;
use Symfony\Component\HttpFoundation\Request;

class TalkController
{
    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $id = $req->get('id');
        $user = $app['sentry']->getUser();

        // @todo Move to the Model layer
        $talk_id = filter_var($id, FILTER_VALIDATE_INT);

        if (empty($talk_id)) {
            return $app->redirect('/dashboard');
        }

        $talk = new Talk($app['db']);
        $talk_info = $talk->findById($talk_id);

        if ($talk_info['user_id'] !== $user->getId()) {
            return $app->redirect('/dashboard');
        }

        return $app['twig']->render('edit_talk.twig', array(
            'formAction'  => '/talk/update',
            'id'          => $talk_id,
            'title'       => $talk_info['title'],
            'description' => $talk_info['description'],
            'type'        => $talk_info['type'],
            'buttonInfo'  => 'Update my talk!',
            'user'        => $user,
        ));
    }

    public function createAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        return $app['twig']->render('create_talk.twig', array(
            'formAction'  => '/talk/create',
            'title'       => $req->get('title'),
            'description' => $req->get('description'),
            'type'        => $req->get('type'),
            'buttonInfo'  => 'Submit my talk!',
            'user'        => $app['sentry']->getUser(),
        ));
    }

    public function processCreateAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();
        $request_data = array(
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'user_id' => $req->get('user_id')
        );

        $form = new TalkForm($request_data, $app['purifier']);

        if (!$form->validateAll()) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->error_messages)
            ));

            $data = array(
                'formAction' => '/talk/create',
                'title' => $req->get('title'),
                'description' => $req->get('description'),
                'type' => $req->get('type'),
                'buttonInfo' => 'Submit my talk!',
                'user' => $user,
                'error_message' => implode('<br>', $form->error_messages)
            );

            return $app['twig']->render('create_talk.twig', $data);
        }

        $sanitized_data = $form->sanitize();
        $data = array(
            'title' => $sanitized_data['title'],
            'description' => $sanitized_data['description'],
            'type' => $sanitized_data['type'],
            'user_id' => (int)$user->getId(),
            'user' => $user
        );

        $talk = new Talk($app['db']);
        if (!$talk->create($data)) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Couldn't create a new record, please try again"
            ));

            // @todo move view data to the templates
            $data['formAction'] = '/talk/create';
            $data['buttonInfo'] = 'Submit my talk!';
            $data['error_message'] = "Unable to create a new record in our talks database, please try again";

            return $app['twig']->render('create_talk.twig', $data);
        }

        $app['session']->getFlashBag()->set('flash', array(
            'type' => 'success',
            'short' => '',
            'ext' => "Succesfully created a talk"
        ));

        return $app->redirect('/dashboard');
    }

    public function updateAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();

        $request_data = array(
            'id' => $req->get('id'),
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'user_id' => $req->get('user_id')
        );

        $form = new TalkForm($request_data, $app['purifier']);
        $valid = $form->validateAll();

        if ($valid) {
            $sanitized_data = $form->sanitize();
            $data = array(
                'id' => (int)$sanitized_data['id'],
                'title' => $sanitized_data['title'],
                'description' => $sanitized_data['description'],
                'type' => $sanitized_data['type'],
                'user_id' => (int)$user->getId()
            );
            $talk = new Talk($app['db']);
            $talk->update($data);
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'success',
                'short' => 'Updated talk!'
            ));
        }

        if (!$valid) {
            $app['session']->getFlashBag()->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->errorMessages)
            ));
        }

        return $app->redirect('/talk/edit/' . $req->get('id'));
    }

    public function deleteAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->json(array('delete' => 'no-user'));
        }

        $talk = new Talk($app['db']);
        if ($talk->delete($req->get('tid'), $req->get('user_id')) === true) {
            return $app->json(array('delete' => 'ok'));
        }

        return $app->json(array('delete' => 'no'));
    }
}
