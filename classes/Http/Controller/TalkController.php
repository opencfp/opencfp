<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Http\Form\TalkForm;
use OpenCFP\Http\View\TalkHelper;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
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
        $helper  = $this->service(TalkHelper::class);
        $options = [
            'categories' => $helper->getTalkCategories(),
            'levels'     => $helper->getTalkLevels(),
            'types'      => $helper->getTalkTypes(),
        ];

        return new TalkForm($requestData, $this->service('purifier'), $options);
    }

    /**
     * Controller action for viewing a specific talk
     *
     * @param Request $req
     *
     * @return mixed
     */
    public function viewAction(Request $req)
    {
        /* @var Speakers $speakers */
        $speakers = $this->service('application.speakers');

        try {
            $talkId   = (int) $req->get('id');
            $talk     = $speakers->getTalk($talkId);
        } catch (NotAuthorizedException $e) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/view.twig', \compact('talkId', 'talk'));
    }

    public function editAction(Request $req)
    {
        $talkId      = (int) $req->get('id');
        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (!$this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended', ]
            );

            return $this->app->redirect($this->url('talk_view', ['id' => $talkId]));
        }

        if (empty($talkId)) {
            return $this->redirectTo('dashboard');
        }

        $userId = $this->service(Authentication::class)->userId();

        $talk = Talk::find($talkId);

        if (!$talk instanceof Talk || (int) $talk['user_id'] !== $userId) {
            return $this->redirectTo('dashboard');
        }

        $csrfTokenManager = $this->service('csrf.token_manager');
        $csrfToken        = new CsrfToken('edit_talk', $req->get('token'));

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->redirectTo('dashboard');
        }

        $helper = $this->service(TalkHelper::class);
        $data   = [
            'formAction'     => $this->url('talk_update'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'id'             => $talkId,
            'title'          => \html_entity_decode($talk['title']),
            'description'    => \html_entity_decode($talk['description']),
            'type'           => $talk['type'],
            'level'          => $talk['level'],
            'category'       => $talk['category'],
            'desired'        => $talk['desired'],
            'slides'         => $talk['slides'],
            'other'          => $talk['other'],
            'sponsor'        => $talk['sponsor'],
            'buttonInfo'     => 'Update my talk!',
        ];

        return $this->render('talk/edit.twig', $data);
    }

    public function createAction(Request $req)
    {
        // You can only create talks while the CfP is open
        if (!$this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }

        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'title'          => $req->get('title'),
            'description'    => $req->get('description'),
            'type'           => $req->get('type'),
            'level'          => $req->get('level'),
            'category'       => $req->get('category'),
            'desired'        => $req->get('desired'),
            'slides'         => $req->get('slides'),
            'other'          => $req->get('other'),
            'sponsor'        => $req->get('sponsor'),
            'buttonInfo'     => 'Submit my talk!',
        ];

        return $this->render('talk/create.twig', $data);
    }

    public function processCreateAction(Request $req)
    {
        // You can only create talks while the CfP is open
        if (!$this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended', ]
            );

            return $this->redirectTo('dashboard');
        }

        $user = $this->service(Authentication::class)->user();

        $request_data = [
            'title'       => $req->get('title'),
            'description' => $req->get('description'),
            'type'        => $req->get('type'),
            'level'       => $req->get('level'),
            'category'    => $req->get('category'),
            'desired'     => $req->get('desired'),
            'slides'      => $req->get('slides'),
            'other'       => $req->get('other'),
            'sponsor'     => $req->get('sponsor'),
            'user_id'     => $req->get('user_id'),
        ];

        $csrfTokenManager = $this->service('csrf.token_manager');
        $csrfToken        = new CsrfToken('speaker_talk', $req->get('_token'));

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->redirectTo('dashboard');
        }

        $form = $this->getTalkForm($request_data);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitizedData            = $form->getCleanData();
            $sanitizedData['user_id'] =  (int) $user->getId();
            $talk                     = Talk::create($sanitizedData);

            $this->service('session')->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => 'Successfully saved talk.',
            ]);

            // send email to speaker showing submission
            $this->sendSubmitEmail($user->getLogin(), (int) $talk->id);

            return $this->redirectTo('dashboard');
        }
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'title'          => $req->get('title'),
            'description'    => $req->get('description'),
            'type'           => $req->get('type'),
            'level'          => $req->get('level'),
            'category'       => $req->get('category'),
            'desired'        => $req->get('desired'),
            'slides'         => $req->get('slides'),
            'other'          => $req->get('other'),
            'sponsor'        => $req->get('sponsor'),
            'buttonInfo'     => 'Submit my talk!',
        ];

        $this->service('session')->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);
        $data['flash'] = $this->service('session')->get('flash');

        return $this->render('talk/create.twig', $data);
    }

    public function updateAction(Request $req)
    {
        if (!$this->service('callforproposal')->isOpen()) {
            $this->service('session')->set(
                'flash',
                [
                    'type'  => 'error',
                    'short' => 'Read Only',
                    'ext'   => 'You cannot edit talks once the call for papers has ended', ]
            );

            return $this->app->redirect($this->url('talk_view', ['id' => $req->get('id')]));
        }

        $csrfTokenManager = $this->service('csrf.token_manager');
        $csrfToken        = new CsrfToken('speaker_talk', $req->get('_token'));

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->redirectTo('dashboard');
        }

        $user = $this->service(Authentication::class)->user();

        $request_data = [
            'id'          => $req->get('id'),
            'title'       => $req->get('title'),
            'description' => $req->get('description'),
            'type'        => $req->get('type'),
            'level'       => $req->get('level'),
            'category'    => $req->get('category'),
            'desired'     => $req->get('desired'),
            'slides'      => $req->get('slides'),
            'other'       => $req->get('other'),
            'sponsor'     => $req->get('sponsor'),
            'user_id'     => $req->get('user_id'),
        ];

        $form = $this->getTalkForm($request_data);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitizedData            = $form->getCleanData();
            $sanitizedData['user_id'] =(int) $user->getId();
            
            if (Talk::find((int) $sanitizedData['id'])->update($sanitizedData)) {
                $this->service('session')->set('flash', [
                    'type'  => 'success',
                    'short' => 'Success',
                    'ext'   => 'Successfully saved talk.',
                ]);

                // send email to speaker showing submission
                $this->sendSubmitEmail($user->getLogin(), (int) $sanitizedData['id']);

                return $this->redirectTo('dashboard');
            }
        }
      
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_update'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'id'             => $req->get('id'),
            'title'          => $req->get('title'),
            'description'    => $req->get('description'),
            'type'           => $req->get('type'),
            'level'          => $req->get('level'),
            'category'       => $req->get('category'),
            'desired'        => $req->get('desired'),
            'slides'         => $req->get('slides'),
            'other'          => $req->get('other'),
            'sponsor'        => $req->get('sponsor'),
            'buttonInfo'     => 'Update my talk!',
        ];

        $this->service('session')->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);

        $data['flash'] = $this->service('session')->get('flash');

        return $this->render('talk/edit.twig', $data);
    }

    public function deleteAction(Request $req)
    {
        // You can only delete talks while the CfP is open
        if (!$this->service('callforproposal')->isOpen()) {
            return $this->app->json(['delete' => 'no']);
        }

        // Reject any attempt to delete a talk without a proper token
        $csrfTokenManager = $this->service('csrf.token_manager');
        $csrfToken        = new CsrfToken('delete_talk', $req->get('token'));

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->app->json(['delete' => 'no']);
        }

        $userId = $this->service(Authentication::class)->userId();
        $talk   = Talk::find($req->get('tid'), ['id', 'user_id']);

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
     * @param int    $talkId
     *
     * @return mixed
     */
    protected function sendSubmitEmail(string $email, int $talkId)
    {
        $talk = Talk::find($talkId, ['title']);

        /* @var Twig_Environment $twig */
        $twig = $this->service('twig');

        // Build our email that we will send
        $template   = $twig->loadTemplate('emails/talk_submit.twig');
        $parameters = [
            'email'   => $this->app->config('application.email'),
            'title'   => $this->app->config('application.title'),
            'talk'    => $talk->title,
            'enddate' => $this->app->config('application.enddate'),
        ];

        try {
            $mailer  = $this->service('mailer');
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
