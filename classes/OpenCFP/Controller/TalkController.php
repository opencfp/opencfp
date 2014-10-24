<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Form\TalkForm;
use OpenCFP\Config\ConfigINIFileLoader;

class TalkController
{
    use FlashableTrait;

    /**
     * Check to see if the CfP for this app is still open
     *
     * @param integer $current_time
     * @return boolean
     */
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

    /**
     * Controller action for viewing a specific talk
     *
     * @param Request $req
     * @param Application $app
     * @return mixed
     */
    public function viewAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $id = $req->get('id');
        $talk_id = filter_var($id, FILTER_VALIDATE_INT);

        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $talk_info = $talk_mapper->get($talk_id);

        $user = $app['sentry']->getUser();

        if ($talk_info['user_id'] !== $user->getId()) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $template = $app['twig']->loadTemplate('talk/view.twig');
        $data = array(
            'id' => $talk_id,
            'talk' => $talk_info,
        );

        return $template->render($data);
    }

    /**
     * Controller action for displaying the form to edit an existing talk
     *
     * @param Request $req
     * @param Application $app
     * @return mixed
     */
    public function editAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $id = $req->get('id');
        $talk_id = filter_var($id, FILTER_VALIDATE_INT);

        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (!$this->isCfpOpen(strtotime('now'))) {
            $app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Read Only',
                'ext' => 'You cannot edit talks once the call for papers has ended']
            );

            return $app->redirect($app['url'] . '/talk/'.$talk_id);
        }

        if (empty($talk_id)) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $user = $app['sentry']->getUser();

        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $talk_info = $talk_mapper->get($talk_id)->toArray();

        if ($talk_info['user_id'] !== (int)$user->getId()) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $template = $app['twig']->loadTemplate('talk/edit.twig');
        $data = array(
            'formAction' => '/talk/update',
            'id' => $talk_id,
            'title' => html_entity_decode($talk_info['title']),
            'description' => html_entity_decode($talk_info['description']),
            'type' => $talk_info['type'],
            'level' => $talk_info['level'],
            'category' => $talk_info['category'],
            'desired' => $talk_info['desired'],
            'slides' => $talk_info['slides'],
            'other' => $talk_info['other'],
            'sponsor' => $talk_info['sponsor'],
            'buttonInfo' => 'Update my talk!',
        );

        return $template->render($data);
    }

    /**
     * Action for displaying the form to create a new talk
     *
     * @param Request $req
     * @param Application $app
     * @return mixed
     */
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
        );

        return $template->render($data);
    }

    /**
     * Controller action the processes the POST request to try and create
     * a new talk
     *
     * @param Request $req
     * @param Application $app
     * @return mixed
     */
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
            );

            $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
            $talk = $talk_mapper->create($data);

            $app['session']->set('flash', array(
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Successfully added talk.',
                ));

            // send email to speaker showing submission
            $this->sendSubmitEmail($app, $user->getLogin(), $talk->id);

            return $app->redirect($app['url'] . '/dashboard');
        }

        if (!$isValid) {
            $template = $app['twig']->loadTemplate('talk/edit.twig');
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

            $mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
            $talk = $mapper->get($data['id']);

            foreach ($data as $field => $value) {
                $talk->$field = $value;
            }

            $mapper->save($talk);

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
            return $app->json(['delete' => 'no-user']);
        }

        // You can only delete talks while the CfP is open
        if (!$this->isCfpOpen(strtotime('now'))) {
            return $app->json(['delete' => 'no']);
        }

        $user = $app['sentry']->getUser();
        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $talk = $talk_mapper->get($req->get('tid'));

        if ($talk->user_id !== (int)$user->getId()) {
            return $app->json(['delete' => 'no']);
        }

        $talk_mapper->delete($talk);

        return $app->json(['delete' => 'ok']);
    }

    /**
     * Method that sends an email when a talk is created
     *
     * @param Application $app
     * @param string $email
     * @param integer $talk_id
     * @return mixed
     */
    protected function sendSubmitEmail(Application $app, $email, $talk_id)
    {
        $mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $talk = $mapper->get($talk_id);

        $config = $app['config'];

        // Build our email that we will send
        $template = $app['twig']->loadTemplate('emails/talk_submit.twig');
        $parameters = array(
            'email' => $config['application.email'],
            'title' => $config['application.title'],
            'talk' => $talk->title,
            'enddate' => $config['application.enddate']
        );

        try {
            $mailer = $app['mailer'];
            $message = new \Swift_Message();

            $message->setTo($email);
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
