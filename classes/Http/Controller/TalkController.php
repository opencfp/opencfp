<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\Authentication;
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
     * @param $request_data
     *
     * @return TalkForm
     */
    private function getTalkForm($request_data)
    {
        $options = [
            'categories' => $this->getTalkCategories(),
            'levels' => $this->getTalkLevels(),
            'types' => $this->getTalkTypes(),
        ];
        $form = new TalkForm($request_data, $this->service('purifier'), $options);
        return $form;
    }
    
    private function getTalkCategories()
    {
        $categories = $this->app->config('talk.categories');
        
        if ($categories === null) {
            $categories = [
                'api' => 'APIs (REST, SOAP, etc.)',
                'continuousdelivery'=> 'Continuous Delivery',
                'database'=> 'Database',
                'development'=> 'Development',
                'devops' => 'Devops',
                'framework' => 'Framework',
                'ibmi' => 'IBMi',
                'javascript' => 'JavaScript',
                'security' => 'Security',
                'testing' => 'Testing',
                'uiux' => 'UI/UX',
                'other' => 'Other',
            ];
        }
        
        return $categories;
    }

    private function getTalkTypes()
    {
        $types = $this->app->config('talk.types');

        if ($types === null) {
            $types = [
                'regular' => 'Regular',
                'tutorial' => 'Tutorial',
            ];
        }

        return $types;
    }

    private function getTalkLevels()
    {
        $levels = $this->app->config('talk.levels');

        if ($levels === null) {
            $levels = [
                'entry' => 'Entry level',
                'mid' => 'Mid-level',
                'advanced' => 'Advanced',
            ];
        }

        return $levels;
    }

    /**
     * Controller action for viewing a specific talk
     *
     * @param  Request $req
     *
     * @return mixed
     */
    public function viewAction(Request $req)
    {
        /* @var Speakers $speakers */
        $speakers = $this->service('application.speakers');

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
     *
     * @return mixed
     */
    public function editAction(Request $req)
    {
        $id = $req->get('id');
        $talk_id = filter_var($id, FILTER_VALIDATE_INT);

        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type' => 'error',
                'short' => 'Read Only',
                'ext' => 'You cannot edit talks once the call for papers has ended', ]
            );

            return $this->app->redirect($this->url('talk_view', ['id' => $talk_id]));
        }

        if (empty($talk_id)) {
            return $this->redirectTo('dashboard');
        }

        $user = $this->service(Authentication::class)->user();

        /* @var Locator $spot */
        $spot = $this->service('spot');

        $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk_info = $talk_mapper->where(['id' => $talk_id])->execute()->first()->toArray();

        if ($talk_info['user_id'] !== (int) $user->getId()) {
            return $this->redirectTo('dashboard');
        }

        $data = [
            'formAction' => $this->url('talk_update'),
            'talkCategories' => $this->getTalkCategories(),
            'talkTypes' => $this->getTalkTypes(),
            'talkLevels' => $this->getTalkLevels(),
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
        ];

        return $this->render('talk/edit.twig', $data);
    }

    /**
     * Action for displaying the form to create a new talk
     *
     * @param  Request $req
     *
     * @return mixed
     */
    public function createAction(Request $req)
    {
        // You can only create talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }

        $data = [
            'formAction' => $this->url('talk_create'),
            'talkCategories' => $this->getTalkCategories(),
            'talkTypes' => $this->getTalkTypes(),
            'talkLevels' => $this->getTalkLevels(),
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
        ];

        return $this->render('talk/create.twig', $data);
    }

    /**
     * Controller action the processes the POST request to try and create
     * a new talk
     *
     * @param  Request $req
     *
     * @return mixed
     */
    public function processCreateAction(Request $req)
    {
        // You can only create talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }

        $user = $this->service(Authentication::class)->user();

        $request_data = [
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'level' => $req->get('level'),
            'category' => $req->get('category'),
            'desired' => $req->get('desired'),
            'slides' => $req->get('slides'),
            'other' => $req->get('other'),
            'sponsor' => $req->get('sponsor'),
            'user_id' => $req->get('user_id'),
        ];

        $form = $this->getTalkForm($request_data);
        $form->sanitize();
        $isValid = $form->validateAll();

        if ($isValid) {
            $sanitized_data = $form->getCleanData();

            /* @var Locator $spot */
            $spot = $this->service('spot');

            $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $data = [
                'title' => $sanitized_data['title'],
                'description' => $sanitized_data['description'],
                'type' => $sanitized_data['type'],
                'level' => $sanitized_data['level'],
                'category' => $sanitized_data['category'],
                'desired' => $sanitized_data['desired'],
                'slides' => $sanitized_data['slides'],
                'other' => $sanitized_data['other'],
                'sponsor' => $sanitized_data['sponsor'],
                'user_id' => (int) $user->getId(),
            ];

            $talk = $talk_mapper->build($data);

            try {
                $talk_mapper->save($talk, ['relations' => true]);
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
            $this->sendSubmitEmail($this->app, $user->getLogin(), $talk->get('id'));

            return $this->redirectTo('dashboard');
        }

        $data = [
            'formAction' => $this->url('talk_create'),
            'talkCategories' => $this->getTalkCategories(),
            'talkTypes' => $this->getTalkTypes(),
            'talkLevels' => $this->getTalkLevels(),
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
        ];

        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => implode('<br>', $form->getErrorMessages()),
        ]);

        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => implode('<br>', $form->getErrorMessages()),
        ]);
        $data['flash'] = $this->getFlash($this->app);

        return $this->render('talk/create.twig', $data);
    }

    public function updateAction(Request $req)
    {
        $user = $this->service(Authentication::class)->user();

        $request_data = [
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
            'user_id' => $req->get('user_id'),
        ];

        $form = $this->getTalkForm($request_data);
        $form->sanitize();
        $isValid = $form->validateAll();

        if ($isValid) {
            $sanitized_data = $form->getCleanData();

            /* @var Locator $spot */
            $spot = $this->service('spot');
            $talk_mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $data = [
                'id' => (int) $sanitized_data['id'],
                'title' => $sanitized_data['title'],
                'description' => $sanitized_data['description'],
                'type' => $sanitized_data['type'],
                'level' => $sanitized_data['level'],
                'category' => $sanitized_data['category'],
                'desired' => $sanitized_data['desired'],
                'slides' => $sanitized_data['slides'],
                'other' => $sanitized_data['other'],
                'sponsor' => $sanitized_data['sponsor'],
                'user_id' => (int) $user->getId(),
                'updated_at' => new \DateTime(),
            ];

            $talk = $talk_mapper->get($data['id']);

            foreach ($data as $field => $value) {
                $talk->$field = $value;
            }

            try {
                $talk_mapper->save($talk, ['relations' => true]);
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
            $this->sendSubmitEmail($this->app, $user->getLogin(), $talk->get('id'));

            return $this->redirectTo('dashboard');
        }

        $data = [
            'formAction' => $this->url('talk_update'),
            'talkCategories' => $this->getTalkCategories(),
            'talkTypes' => $this->getTalkTypes(),
            'talkLevels' => $this->getTalkLevels(),
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
        ];

        $this->service('session')->set('flash', [
            'type' => 'error',
            'short' => 'Error',
            'ext' => implode('<br>', $form->getErrorMessages()),
        ]);

        $data['flash'] = $this->getFlash($this->app);

        return $this->render('talk/edit.twig', $data);
    }

    public function deleteAction(Request $req, Application $app)
    {
        // You can only delete talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            return $app->json(['delete' => 'no']);
        }

        $user = $this->service(Authentication::class)->user();
        $talk_mapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $talk = $talk_mapper->get($req->get('tid'));

        if ($talk->user_id !== (int) $user->getId()) {
            return $app->json(['delete' => 'no']);
        }

        $talk_mapper->delete($talk);

        return $app->json(['delete' => 'ok']);
    }

    /**
     * Method that sends an email when a talk is created
     *
     * @param  Application $app
     * @param  string      $email
     * @param  integer     $talk_id
     *
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
