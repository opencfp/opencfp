<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Form\TalkForm;
use OpenCFP\Http\View\TalkHelper;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class TalkController extends BaseController
{
    /**
     * @param $requestData
     *
     * @return TalkForm
     */
    private function getTalkForm($requestData): TalkForm
    {
        $helper = $this->service(TalkHelper::class);
        $options = [
            'categories' => $helper->getTalkCategories(),
            'levels' => $helper->getTalkLevels(),
            'types' => $helper->getTalkTypes(),
        ];

        return new TalkForm($requestData, $this->service('purifier'), $options);
    }

    public function viewAction(Request $req)
    {
        /* @var Speakers $speakers */
        $speakers = $this->service('application.speakers');

        try {
            $id = (int) $req->get('id');
            $talk = $speakers->getTalk($id);
        } catch (NotAuthorizedException $e) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/view.twig', compact('id', 'talk'));
    }

    public function editAction(Request $req)
    {
        $talk_id = (int) $req->get('id');

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

        $userId = $this->service(Authentication::class)->userId();

        $talk_info = Talk::find($talk_id);

        if (!$talk_info instanceof Talk || (int) $talk_info['user_id'] !== $userId) {
            return $this->redirectTo('dashboard');
        }
        $helper = $this->service(TalkHelper::class);
        $data = [
            'formAction' => $this->url('talk_update'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes' => $helper->getTalkTypes(),
            'talkLevels' => $helper->getTalkLevels(),
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

        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction' => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes' => $helper->getTalkTypes(),
            'talkLevels' => $helper->getTalkLevels(),
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

        if ($form->validateAll()) {
            $sanitized_data = $form->getCleanData();
            $sanitized_data['user_id'] =  (int) $user->getId();
            $talk = Talk::create($sanitized_data);

            $this->service('session')->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => 'Successfully saved talk.',
            ]);

            // send email to speaker showing submission
            $this->sendSubmitEmail($user->getLogin(), (int) $talk->id);

            return $this->redirectTo('dashboard');
        }
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction' => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes' => $helper->getTalkTypes(),
            'talkLevels' => $helper->getTalkLevels(),
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
        $data['flash'] = $this->service('session')->get('flash');

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

        if ($form->validateAll()) {
            $sanitized_data = $form->getCleanData();
            $sanitized_data['user_id'] =(int) $user->getId();
            
            if (Talk::find((int) $sanitized_data['id'])->update($sanitized_data)) {
                $this->service('session')->set('flash', [
                    'type' => 'success',
                    'short' => 'Success',
                    'ext' => 'Successfully saved talk.',
                ]);

                // send email to speaker showing submission
                $this->sendSubmitEmail($user->getLogin(), (int) $sanitized_data['id']);

                return $this->redirectTo('dashboard');
            }
        }
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction' => $this->url('talk_update'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes' => $helper->getTalkTypes(),
            'talkLevels' => $helper->getTalkLevels(),
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

        $data['flash'] = $this->service('session')->get('flash');

        return $this->render('talk/edit.twig', $data);
    }

    public function deleteAction(Request $req)
    {
        // You can only delete talks while the CfP is open
        if (! $this->service('callforproposal')->isOpen()) {
            return $this->app->json(['delete' => 'no']);
        }

        $userId = $this->service(Authentication::class)->userId();
        $talk = Talk::find($req->get('tid'), ['user_id']);

        if ((int) $talk->user_id !==  $userId) {
            return $this->app->json(['delete' => 'no']);
        }

        $talk->delete();

        return $this->app->json(['delete' => 'ok']);
    }

    /**
     * Method that sends an email when a talk is created
     *
     * @param string $email
     * @param int    $talk_id
     *
     * @return mixed
     */
    protected function sendSubmitEmail(string $email, int $talk_id)
    {
        $talk = Talk::find($talk_id, ['title']);

        /* @var Twig_Environment $twig */
        $twig = $this->service('twig');

        // Build our email that we will send
        $template = $twig->loadTemplate('emails/talk_submit.twig');
        $parameters = [
            'email' => $this->app->config('application.email'),
            'title' => $this->app->config('application.title'),
            'talk' => $talk->title,
            'enddate' => $this->app->config('application.enddate'),
        ];

        try {
            $mailer = $this->service('mailer');
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
