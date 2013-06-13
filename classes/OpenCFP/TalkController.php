<?php
namespace OpenCFP;

use Silex\Application;
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
        $talk_id= filter_var($id, FILTER_VALIDATE_INT);

        if (empty($talk_id)) {
            return $app->redirect('/dashboard');
        }
            
        $talk = new \OpenCFP\Talk($app['db']);
        $talk_info = $talk->findById($talk_id);

        if ($talk_info['user_id'] !== $user->getId()) {
            return $app->redirect('/dashboard');
        }

        $template_name = 'edit_talk.twig';
        $template = $app['twig']->loadTemplate($template_name);
        $data = array(
            'formAction' => '/talk/create',
            'id' => $talk_id,
            'title' => $talk_info['title'],
            'description' => $talk_info['description'],
            'type' => $talk_info['type'],
            'buttonInfo' => 'Update my talk!',
            'user' => $user
        );

        return $template->render($data);
    }

    public function createAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $request_data = array(
            'id' => $request->get('id'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'type' => $request->get('type'),
            'user_id' => $request->get('user_id')
        );
        $form = new \OpenCFP\TalkForm($request_data);
        
        if ($form->validateAll()) {
            $sanitized_data = $form->sanitize();
            $data = array(
                'id' => (int)$sanitized_data['id'],
                'title' => $sanitized_data['title'],
                'description' => $sanitized_data['description'],
                'type' => $sanitized_data['type'],
                'user_id' => (int)$user->getId()
            );
            $talk = new \OpenCFP\Talk($app['db']);
            $talk->update($data);
            $app['session']->set('flash', array(
                'type' => 'success',
                'short' => 'Updated talk!'
            ));
        }

        if (!$valid) {
            $app['session']->set('flash', array(
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->errorMessages)
            ));
        }
        
        return $app->redirect('/talk/edit/' . $request->get('id'));

    }
}
