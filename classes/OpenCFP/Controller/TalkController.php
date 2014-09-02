<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Form\TalkForm;
use OpenCFP\Model\Talk;
use OpenCFP\Config\ConfigINIFileLoader;

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

    public function isCfpOpen($current_time)
    {
        $loader = new ConfigINIFileLoader(
            APP_DIR . '/config/config.' . APP_ENV . '.ini'
        );
        $config_data = $loader->load();
        $end_date = $config_data['application']['enddate'] . ' 11:59 PM';

        if ($current_time < strtotime($end_date)) {
            return true;
        }

        return false;
    }

    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        // You can only edit talks while the CfP is open
        if (!$this->isCfpOpen(strtotime('now'))) {
            $app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot edit talks once the call for papers has ended']
            );

            return $app->redirect($app['url'] . '/dashboard');
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

        // You can only create talks while the CfP is open
        if (!$this->isCfpOpen(strtotime('now'))) {
            $app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended']
            );

            return $app->redirect($app['url'] . '/dashboard');
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
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        // You can only create talks while the CfP is open
        if (!$this->isCfpOpen(strtotime('now'))) {
            $app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended']
            );

            return $app->redirect($app['url'] . '/dashboard');
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
        $isValid = $form->validateAll();

        if ($isValid) {
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
            $talk->create($data);

            $app['session']->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Successfully added talk.',
                ));

            // send email to speaker showing submission
            $this->sendSubmitEmail($app, $user, $app['db']->lastInsertId());

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
                'buttonInfo' => 'Submit my talk!',
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

        // You can only delete talks while the CfP is open
        if (!$this->isCfpOpen(strtotime('now'))) {
            return $app->json(array('delete' => 'no'));
        }

        $user = $app['sentry']->getUser();
        $talk = new Talk($app['db']);

        if ($talk->delete($req->get('tid'), $user->getId()) === true) {
            return $app->json(array('delete' => 'ok'));
        }

        return $app->json(array('delete' => 'no'));
    }

    protected function sendSubmitEmail(Application $app, $user, $talk_id)
    {
        $talk = new Talk($app['db']);
        $talk_info = $talk->findById($talk_id);

        // Create our Mailer object
        $loader = new ConfigINIFileLoader(
            APP_DIR . '/config/config.' . APP_ENV . '.ini'
        );
        $config_data = $loader->load();
        $transport = new \Swift_SmtpTransport(
            $config_data['smtp']['host'],
            $config_data['smtp']['port']
        );

        if (!empty($config_data['smtp']['user'])) {
            $transport->setUsername($config_data['smtp']['user'])
                      ->setPassword($config_data['smtp']['password']);
        }

        if (!empty($config_data['smtp']['encryption'])) {
            $transport->setEncryption($config_data['smtp']['encryption']);
        }

        // Build our email that we will send
        $template = $app['twig']->loadTemplate('emails/talk_submit.twig');
        $parameters = array(
            'email' => $config_data['application']['email'],
            'title' => $config_data['application']['title'],
            'talk' => $talk_info['title'],
            'enddate' => $config_data['application']['enddate']
        );

        try {
            $mailer = new \Swift_Mailer($transport);
            $message = new \Swift_Message();

            $message->setTo($user['email']);
            $message->setFrom(
                $template->renderBlock('from', $parameters),
                $template->renderBlock('from_name', $parameters)
            );

            $message->setSubject($template->renderBlock('subject', $parameters));
            $message->setBody($template->renderBlock('body_text', $parameters));
            $message->addPart(
                $template->renderBlock('body_html', $parameters),
                'text/html'
            );

            return $mailer->send($message);
        } catch (\Exception $e) {
            echo $e;die();
        }
    }
}
