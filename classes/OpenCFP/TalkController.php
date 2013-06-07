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
        $talkId = filter_var($id, FILTER_VALIDATE_INT);

        if (empty($talkId)) {
            return $app->redirect('/dashboard');
        }
            
        $talk = new \OpenCFP\Talk($app['db']);
        $talkInfo = $talk->findById($talkId);

        if ($talkInfo['user_id'] !== $user->getId()) {
            return $app->redirect('/dashboard');
        }

        $templateName = 'edit_talk.twig';
        $template = $app['twig']->loadTemplate($templateName);
        $data = array(
            'formAction' => '/talk/create',
            'id' => $talkId,
            'title' => $talkInfo['title'],
            'description' => $talkInfo['description'],
            'type' => $talkInfo['type'],
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

        $requestData = array(
            'id' => $request->get('id'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'type' => $request->get('type'),
            'user_id' => $request->get('user_id')
        );
        $form = new \OpenCFP\TalkForm($requestData);
        
        if ($form->validateAll()) {
            $sanitizedData = $form->sanitize();
            $data = array(
                'id' => (int)$sanitizedData['id'],
                'title' => $sanitizedData['title'],
                'description' => $sanitizedData['description'],
                'type' => $sanitizedData['type'],
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
