<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Form\TalkForm;
use OpenCFP\Http\View\TalkHelper;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
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
        /** @var TalkHelper $helper */
        $helper = $this->service(TalkHelper::class);

        $options = [
            'categories' => $helper->getTalkCategories(),
            'levels'     => $helper->getTalkLevels(),
            'types'      => $helper->getTalkTypes(),
        ];

        /** @var \HTMLPurifier $htmlPurifier */
        $htmlPurifier = $this->service('purifier');

        return new TalkForm($requestData, $htmlPurifier, $options);
    }

    /**
     * Controller action for viewing a specific talk
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function viewAction(Request $request)
    {
        /* @var Speakers $speakers */
        $speakers = $this->service('application.speakers');

        try {
            $talkId = (int) $request->get('id');
            $talk   = $speakers->getTalk($talkId);
        } catch (NotAuthorizedException $e) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/view.twig', \compact('talkId', 'talk'));
    }

    public function editAction(Request $request)
    {
        $talkId = (int) $request->get('id');

        /** @var CallForPapers $callForPapers */
        $callForPapers = $this->service(CallForPapers::class);

        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (!$callForPapers->isOpen()) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended',
            ]);

            return $this->app->redirect($this->url('talk_view', ['id' => $talkId]));
        }

        if (empty($talkId)) {
            return $this->redirectTo('dashboard');
        }

        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        $userId = $authentication->userId();

        $talk = Talk::find($talkId);

        if (!$talk instanceof Talk || (int) $talk['user_id'] !== $userId) {
            return $this->redirectTo('dashboard');
        }

        /** @var TalkHelper $helper */
        $helper = $this->service(TalkHelper::class);

        $data = [
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

    public function createAction(Request $request)
    {
        /** @var CallForPapers $callForPapers */
        $callForPapers = $this->service(CallForPapers::class);

        // You can only create talks while the CfP is open
        if (!$callForPapers->isOpen()) {
            /** @var Session\Session $session */
            $session = $this->service('session');

            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended',
            ]);

            return $this->redirectTo('dashboard');
        }

        /** @var TalkHelper $helper */
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'title'          => $request->get('title'),
            'description'    => $request->get('description'),
            'type'           => $request->get('type'),
            'level'          => $request->get('level'),
            'category'       => $request->get('category'),
            'desired'        => $request->get('desired'),
            'slides'         => $request->get('slides'),
            'other'          => $request->get('other'),
            'sponsor'        => $request->get('sponsor'),
            'buttonInfo'     => 'Submit my talk!',
        ];

        return $this->render('talk/create.twig', $data);
    }

    public function processCreateAction(Request $request)
    {
        /** @var CallForPapers $callForPapers */
        $callForPapers = $this->service(CallForPapers::class);

        /** @var Session\Session $session */
        $session = $this->service('session');

        // You can only create talks while the CfP is open
        if (!$callForPapers->isOpen()) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended',
            ]);

            return $this->redirectTo('dashboard');
        }

        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        $user = $authentication->user();

        $requestData = [
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'type'        => $request->get('type'),
            'level'       => $request->get('level'),
            'category'    => $request->get('category'),
            'desired'     => $request->get('desired'),
            'slides'      => $request->get('slides'),
            'other'       => $request->get('other'),
            'sponsor'     => $request->get('sponsor'),
            'user_id'     => $request->get('user_id'),
        ];

        $form = $this->getTalkForm($requestData);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitizedData            = $form->getCleanData();
            $sanitizedData['user_id'] = (int) $user->getId();
            $talk                     = Talk::create($sanitizedData);

            $session->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => 'Successfully saved talk.',
            ]);

            // send email to speaker showing submission
            $this->sendSubmitEmail($user->getLogin(), (int) $talk->id);

            return $this->redirectTo('dashboard');
        }

        /** @var TalkHelper $helper */
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_create'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'title'          => $request->get('title'),
            'description'    => $request->get('description'),
            'type'           => $request->get('type'),
            'level'          => $request->get('level'),
            'category'       => $request->get('category'),
            'desired'        => $request->get('desired'),
            'slides'         => $request->get('slides'),
            'other'          => $request->get('other'),
            'sponsor'        => $request->get('sponsor'),
            'buttonInfo'     => 'Submit my talk!',
        ];

        $session->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);

        $data['flash'] = $session->get('flash');

        return $this->render('talk/create.twig', $data);
    }

    public function updateAction(Request $request)
    {
        /** @var CallForPapers $callForPapers */
        $callForPapers = $this->service(CallForPapers::class);

        /** @var Session\Session $session */
        $session = $this->service('session');

        if (!$callForPapers->isOpen()) {
            $session->set('flash', [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended',
            ]);

            return $this->app->redirect($this->url('talk_view', ['id' => $request->get('id')]));
        }

        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        $user = $authentication->user();

        $requestData = [
            'id'          => $request->get('id'),
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'type'        => $request->get('type'),
            'level'       => $request->get('level'),
            'category'    => $request->get('category'),
            'desired'     => $request->get('desired'),
            'slides'      => $request->get('slides'),
            'other'       => $request->get('other'),
            'sponsor'     => $request->get('sponsor'),
            'user_id'     => $request->get('user_id'),
        ];

        $form = $this->getTalkForm($requestData);
        $form->sanitize();

        if ($form->validateAll()) {
            $sanitizedData            = $form->getCleanData();
            $sanitizedData['user_id'] = (int) $user->getId();
            
            if (Talk::find((int) $sanitizedData['id'])->update($sanitizedData)) {
                $session->set('flash', [
                    'type'  => 'success',
                    'short' => 'Success',
                    'ext'   => 'Successfully saved talk.',
                ]);

                // send email to speaker showing submission
                $this->sendSubmitEmail($user->getLogin(), (int) $sanitizedData['id']);

                return $this->redirectTo('dashboard');
            }
        }

        /** @var TalkHelper $helper */
        $helper = $this->service(TalkHelper::class);

        $data = [
            'formAction'     => $this->url('talk_update'),
            'talkCategories' => $helper->getTalkCategories(),
            'talkTypes'      => $helper->getTalkTypes(),
            'talkLevels'     => $helper->getTalkLevels(),
            'id'             => $request->get('id'),
            'title'          => $request->get('title'),
            'description'    => $request->get('description'),
            'type'           => $request->get('type'),
            'level'          => $request->get('level'),
            'category'       => $request->get('category'),
            'desired'        => $request->get('desired'),
            'slides'         => $request->get('slides'),
            'other'          => $request->get('other'),
            'sponsor'        => $request->get('sponsor'),
            'buttonInfo'     => 'Update my talk!',
        ];

        $session->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);

        $data['flash'] = $session->get('flash');

        return $this->render('talk/edit.twig', $data);
    }

    public function deleteAction(Request $request)
    {
        /** @var CallForPapers $callForPapers */
        $callForPapers = $this->service(CallForPapers::class);

        // You can only delete talks while the CfP is open
        if (!$callForPapers->isOpen()) {
            return $this->app->json(['delete' => 'no']);
        }

        /** @var Authentication $authentication */
        $authentication = $this->service(Authentication::class);

        $userId = $authentication->userId();
        $talk   = Talk::find($request->get('tid'), ['id', 'user_id']);

        if ((int) $talk->user_id !== $userId) {
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
            /** @var \Swift_Mailer $mailer */
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
