<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Form\TalkForm;
use OpenCFP\Model\Talk;

class TalkController
{
    public function getFlash(Application $app)
    {
        $flash = $app['session']->get('flash');
        $this->clearFlash($app);
        return $flash;
    }

    public function clearFlash(Application $app)
    {
        $app['session']->set('flash', null);
    }

    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $id = $req->get('id');
        $user = $app['sentry']->getUser();
        $talk_id= filter_var($id, FILTER_VALIDATE_INT);

        if (empty($talk_id)) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $talk = new Talk($app['db']);
        $talk_info = $talk->findById($talk_id);

        if ($talk_info['user_id'] !== $user->getId()) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $template = $app['twig']->loadTemplate('talk/edit.twig');
        $data = array(
            'formAction' => '/talk/update',
            'id' => $talk_id,
            'title' => $talk_info['title'],
            'description' => $talk_info['description'],
            'type' => $talk_info['type'],
            'level' => $talk_info['level'],
            'category' => $talk_info['category'],
            'desired' => $talk_info['desired'],
            'slides' => $talk_info['slides'],
            'other' => $talk_info['other'],
            'sponsor' => $talk_info['sponsor'],
            'buttonInfo' => 'Update my talk!',
            'user' => $user
        );

        return $template->render($data);
    }

    public function createAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();

        $template = $app['twig']->loadTemplate('talk/create.twig');
        $data = array(
            'formAction' => '/talk/create',
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'level' => $req->get('level'),
            'category' => $req->get('category'),
            'desired' => $req->get('desired'),
            'slides' => $req->get('slides'),
            'other' => $req->get('other'),
            'sponsor' => $req->get('sponsor'),
            'buttonInfo' => 'Submit my talk!',
            'user' => $user
        );

        return $template->render($data);
    }

    public function processCreateAction(Request $req, Application $app)
    {
        $error = 0;
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();
        $request_data = array(
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'level' => $req->get('level'),
            'category' => $req->get('category'),
            'desired' => $req->get('desired'),
            'slides' => $req->get('slides'),
            'other' => $req->get('other'),
            'sponsor' => $req->get('sponsor'),
            'user_id' => $req->get('user_id')
        );

        $form = new TalkForm($request_data, $app['purifier']);
        $form->sanitize();

        if (!$form->validateAll()) {
            $error++;
            $data = array(
                'formAction' => '/talk/create',
                'title' => $req->get('title'),
                'description' => $req->get('description'),
                'type' => $req->get('type'),
                'level' => $req->get('level'),
                'category' => $req->get('category'),
                'desired' => $req->get('desired'),
                'slides' => $req->get('slides'),
                'other' => $req->get('other'),
                'sponsor' => $req->get('sponsor'),
                'buttonInfo' => 'Submit my talk!',
                'user' => $user,
            );
        }

        $sanitized_data = $form->getCleanData();
        $data = array(
            'title' => $sanitized_data['title'],
            'description' => $sanitized_data['description'],
            'type' => $sanitized_data['type'],
            'level' => $sanitized_data['level'],
            'category' => $sanitized_data['category'],
            'desired' => $sanitized_data['desired'],
            'slides' => $sanitized_data['slides'],
            'other' => $sanitized_data['other'],
            'sponsor' => $sanitized_data['sponsor'],
            'user_id' => (int)$user->getId(),
            'user' => $user
        );
        $talk = new Talk($app['db']);

        if (!$talk->create($data)) {
            $error++;
            // Set Success Flash Message
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => "Unable to create a new record in our talks database, please try again",
            ));
        }

        // If any errors were found
        if ($error > 0) {
            $data['flash'] = $this->getFlash($app);
            $template = $app['twig']->loadTemplate('talk/create.twig');
            return $template->render($data);
        }

        $app['session']->set('flash', array(
            'type' => 'success',
            'short' => 'Success',
            'ext' => "Succesfully created a talk"
        ));

        return $app->redirect($app['url'] . '/dashboard');
    }

    public function updateAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();

        $request_data = array(
            'id' => $req->get('id'),
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'level' => $req->get('level'),
            'category' => $req->get('category'),
            'desired' => $req->get('desired'),
            'slides' => $req->get('slides'),
            'other' => $req->get('other'),
            'sponsor' => $req->get('sponsor'),
            'user_id' => $req->get('user_id')
        );

        $form = new TalkForm($request_data, $app['purifier']);
        $form->sanitize();
        $isValid = $form->validateAll();

        if ($isValid) {
            $sanitized_data = $form->getCleanData();
            $data = array(
                'id' => (int)$sanitized_data['id'],
                'title' => $sanitized_data['title'],
                'description' => $sanitized_data['description'],
                'type' => $sanitized_data['type'],
                'level' => $sanitized_data['level'],
                'category' => $sanitized_data['category'],
                'desired' => $sanitized_data['desired'],
                'slides' => $sanitized_data['slides'],
                'other' => $sanitized_data['other'],
                'sponsor' => $sanitized_data['sponsor'],
                'user_id' => (int)$user->getId()
            );
            $talk = new Talk($app['db']);
            $talk->update($data);
            $app['session']->set('flash', array(
                'type' => 'success',
                'short' => 'Success',
                'ext' => 'Successfully updated talk.',
            ));

            return $app->redirect($app['url'] . '/dashboard');
        }

        if (!$isValid) {
            $template = $app['twig']->loadTemplate('talk/edit.twig');
            $data = array(
                'formAction' => '/talk/update',
                'id' => $req->get('id'),
                'title' => $req->get('title'),
                'description' => $req->get('description'),
                'type' => $req->get('type'),
                'level' => $req->get('level'),
                'category' => $req->get('category'),
                'desired' => $req->get('desired'),
                'slides' => $req->get('slides'),
                'other' => $req->get('other'),
                'sponsor' => $req->get('sponsor'),
                'buttonInfo' => 'Update my talk!',
                'user' => $user,
            );

            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode("<br>", $form->getErrorMessages())
            ));
        }

        $data['flash'] = $this->getFlash($app);

        return $template->render($data);
    }

    public function deleteAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->json(array('delete' => 'no-user'));
        }

        $user = $app['sentry']->getUser();
        $talk = new Talk($app['db']);

        if ($talk->delete($req->get('tid'), $req->get('user_id')) === true) {
            return $app->json(array('delete' => 'ok'));
        }

        return $app->json(array('delete' => 'no'));
    }
}
