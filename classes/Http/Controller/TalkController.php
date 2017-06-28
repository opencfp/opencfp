<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Http\Form\Entity\Talk;
use OpenCFP\Http\Form\TalkForm;
use Silex\Application;
use Spot\Locator;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class TalkController extends BaseController
{
    use FlashableTrait;

    /**
     * Controller action for viewing a specific talk
     *
     * @param  Request $req
     * @return mixed
     */
    public function viewAction(Request $req)
    {
        $speakers = $this->service('application.speakers');

        if (!$this->service('sentinel')->check()) {
            return $this->redirectTo('login');
        }

        try {
            $id = filter_var($req->get('id'), FILTER_VALIDATE_INT);
            $talk = $speakers->getTalk($id);
        } catch (NotAuthorizedException $e) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/view.twig', compact('id', 'talk'));
    }

    /**
     * Controller action for displaying the form to edit an existing talk
     *
     * @param  Request $req
     * @return mixed
     */
    public function editAction(Request $req)
    {
        $user = $this->service('sentinel')->check();

        if (!$user) {
            return $this->redirectTo('login');
        }

        $talk_id = $req->get('id');

        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Read Only',
                'ext' => 'You cannot edit talks once the call for papers has ended', ]
            );

            return $this->app->redirect($this->url('talk_view', ['id' => $talk_id]));
        }

        if (empty($talk_id)) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk_info = $talk_mapper->where(['id' => $talk_id])->execute()->first()->toArray();

        if ($talk_info['user_id'] !== $user['id']) {
            return $this->redirectTo('dashboard');
        }

        // Create our Talk entity and then pass it into the form factory
        $talk = new Talk();
        $talk->createFromArray($talk_info);
        $form = $this->service('form.factory')
            ->createBuilder(TalkForm::class, $talk, $this->createTalkOptions())
            ->getForm();
        $data = [
            'formAction' => $this->url('talk_update'),
            'form' => $form->createView(),
            'buttonInfo' => 'Update my talk!',
        ];

        return $this->render('talk/edit.twig', $data);
    }

    /**
     * Action for displaying the form to create a new talk
     *
     * @param  Request $req
     * @return mixed
     */
    public function createAction(Request $req)
    {
        $sentinel = $this->service('sentinel');

        if (!$sentinel->check()) {
            return $this->redirectTo('login');
        }

        // You can only create talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }
        $talk = new Talk();
        $form = $this->service('form.factory')
            ->createBuilder(TalkForm::class, $talk, $this->createTalkOptions())
            ->getForm();
        $data = [
            'formAction' => $this->url('talk_create'),
            'form' => $form->createView(),
            'buttonInfo' => 'Submit my talk!',
        ];

        return $this->render('talk/create.twig', $data);
    }

    /**
     * Controller action the processes the POST request to try and create
     * a new talk
     *
     * @param  Request $req
     * @return mixed
     */
    public function processCreateAction(Request $req)
    {
        $user = $this->service('sentinel')->check();

        if (!$user) {
            return $this->redirectTo('login');
        }

        // You can only create talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }

        $form = $this->service('form.factory')
            ->createBuilder(TalkForm::class, new Talk, $this->createTalkOptions())
            ->getForm();
        $form->handleRequest($req);

        if (!$form->isValid()) {
            $data = [
                'formAction' => $this->url('talk_create'),
                'form' => $form->createView(),
                'buttonInfo' => 'Submit my talk!',
            ];
            return $this->render('talk/create.twig', $data);
        }

        $talk_entity = $form->getData();
        $spot = $this->service('spot');
        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $data = [
            'title' => $talk_entity->getTitle(),
            'description' => $talk_entity->getDescription(),
            'type' => $talk_entity->getType(),
            'level' => $talk_entity->getLevel(),
            'category' => $talk_entity->getCategory(),
            'desired' => (int)$talk_entity->getDesired(),
            'slides' => $talk_entity->getSlides(),
            'other' => $talk_entity->getOther(),
            'sponsor' => (int)$talk_entity->getSponsor(),
            'user_id' => (int) $user['id'],
        ];

        // send email to speaker showing submission
        $this->sendSubmitEmail($this->app, $user->getLogin(), $talk->get('id'));

        try {
            $talk_data = $talk_mapper->save($talk, ['relations' => true]);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => 'Successfully saved talk.',
        ]);

        // send email to speaker showing submission
        $this->sendSubmitEmail($this->app, $user['email'], $talk_data);

        return $this->redirectTo('dashboard');
    }

    public function updateAction(Request $req)
    {
        $user = $this->service('sentinel')->check();

        if (!$user) {
            return $this->redirectTo('login');
        }

        $form = $this->service('form.factory')
            ->createBuilder(TalkForm::class, new Talk, $this->createTalkOptions())
            ->getForm();
        $form->handleRequest($req);

        if ($form->isValid()) {
            /* @var Talk $talk_form_entity */
            $talk_form_entity = $form->getData();
            $spot = $this->service('spot');
            $mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $talk = $mapper->get($talk_form_entity->getId());
            $talk->title = (string) $talk_form_entity->getTitle();
            $talk->description = (string) $talk_form_entity->getDescription();
            $talk->type = (string) $talk_form_entity->getType();
            $talk->level = (string) $talk_form_entity->getLevel();
            $talk->category = (string) $talk_form_entity->getCategory();
            $talk->desired = (int) $talk_form_entity->getDesired();
            $talk->slides = $talk_form_entity->getSlides();
            $talk->other = (string) $talk_form_entity->getOther();
            $talk->sponsor = (int) $talk_form_entity->getSponsor();
            $talk->user_id = $talk_form_entity->getUserId();
            $talk->updated_at = new \DateTime();

            try {
                $talk_mapper->save($talk, ['relations' => true]);
            } catch (\Exception $e) {
                $this->service('session')->set('flash', [
                    'type' => 'error',
                    'short' => 'Error',
                    'ext' => 'Unable to update the talk',
                ]);
                $data = [
                    'formAction' => $this->url('talk_update'),
                    'form' => $form->createView(),
                    'buttonInfo' => 'Update my talk!',
                ];

                return $this->render('talk/edit.twig', $data);
            }

            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => 'Successfully saved talk.',
            ]);

            // send email to speaker showing submission
            $this->sendSubmitEmail($this->app, $user->getLogin(), $talk->get('id'));

            return $this->redirectTo('dashboard');
        }

        $data = [
            'formAction' => $this->url('talk_update'),
            'form' => $form->createView(),
            'buttonInfo' => 'Update my talk!',
        ];

        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => 'Please check your form for errors',
        ]);

        $data['flash'] = $this->getFlash($this->app);

        return $this->render('talk/edit.twig', $data);
    }

    public function deleteAction(Request $req, Application $app)
    {
        $user = $this->service('sentinel')->check();

        if (!$user) {
            return $app->json(['delete' => 'no-user']);
        }

        // You can only delete talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            return $app->json(['delete' => 'no']);
        }

        $talk_mapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk = $talk_mapper->get($req->get('tid'));

        if ($talk->user_id !== (int) $user['id']) {
            return $app->json(['delete' => 'no']);
        }

        $talk_mapper->delete($talk);

        return $app->json(['delete' => 'ok']);
    }

    private function createTalkOptions()
    {
        $types = [];
        $categories = [];
        $levels = [];
        $options = [];

        foreach ($this->app->config('talk.types') as $desc => $key) {
            $types[$key] = $desc;
        }

        foreach ($this->app->config('talk.categories') as $desc => $key) {
            $categories[$key] = $desc;
        }

        foreach ($this->app->config('talk.levels') as $desc => $key) {
            $levels[$key] = $desc;
        }

        $options['types'] = $types;
        $options['categories'] = $categories;
        $options['levels'] = $levels;

        return $options;
    }

    /**
     * Method that sends an email when a talk is created
     *
     * @param  Application $app
     * @param  string      $email
     * @param  integer     $talk_id
     * @return mixed
     */
    protected function sendSubmitEmail(Application $app, $email, $talk_id)
    {
        /* @var Locator $spot */
        $spot = $app['spot'];

        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk = $mapper->get($talk_id);

        /* @var Twig_Environment $twig */
        $twig = $app['twig'];

        // Build our email that we will send
        $template = $twig->loadTemplate('emails/talk_submit.twig');
        $parameters = [
            'email' => $this->app->config('application.email'),
            'title' => $this->app->config('application.title'),
            'talk' => $talk->title,
            'enddate' => $this->app->config('application.enddate'),
        ];

        try {
            $mailer = $app['mailer'];
            $message = new Swift_Message();

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
            echo $e;
            die();
        }
    }
}
