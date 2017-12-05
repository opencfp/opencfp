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

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\View\TalkHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class TalkFormController extends BaseController
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var TalkHelper
     */
    private $talkHelper;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    public function __construct(
        Authentication $authentication,
        TalkHelper $talkHelper,
        CallForPapers $callForPapers,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->talkHelper     = $talkHelper;
        $this->callForPapers  = $callForPapers;

        parent::__construct($twig, $urlGenerator);
    }

    public function editAction(Request $request)
    {
        $talkId = (int) $request->get('id');
        // You can only edit talks while the CfP is open
        // This will redirect to "view" the talk in a read-only template
        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended',
            ]);

            return new RedirectResponse($this->url('talk_view', ['id' => $talkId]));
        }

        if (empty($talkId)) {
            return $this->redirectTo('dashboard');
        }

        $userId = $this->authentication->user()->getId();

        $talk = Talk::find($talkId);

        if (!$talk instanceof Talk || (int) $talk['user_id'] !== $userId) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/edit.twig', [
            'formAction'     => $this->url('talk_update'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'talkLevels'     => $this->talkHelper->getTalkLevels(),
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
        ]);
    }

    public function createAction(Request $request)
    {
        // You can only create talks while the CfP is open
        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended',
            ]);

            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/create.twig', [
            'formAction'     => $this->url('talk_create'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'talkLevels'     => $this->talkHelper->getTalkLevels(),
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
        ]);
    }

    public function deleteAction(Request $request)
    {
        // You can only delete talks while the CfP is open
        if (!$this->callForPapers->isOpen()) {
            return new JsonResponse(['delete' => 'no']);
        }

        $userId = $this->authentication->user()->getId();
        $talk   = Talk::find($request->get('tid'), ['id', 'user_id']);

        if ((int) $talk->user_id !== $userId) {
            return new JsonResponse(['delete' => 'no']);
        }

        $talk->delete();

        return new JsonResponse(['delete' => 'ok']);
    }
}
