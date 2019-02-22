<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class TalksController extends BaseController
{
    /**
     * @var TalkHandler
     */
    private $talkHandler;

    public function __construct(TalkHandler $talkHandler, Twig_Environment $twig, UrlGeneratorInterface $urlGenerator)
    {
        $this->talkHandler = $talkHandler;

        parent::__construct($twig, $urlGenerator);
    }

    /**
     * Set Favorited Talk [POST]
     *
     * @param Request $request Request Object
     *
     * @return Response
     */
    public function favoriteAction(Request $request): Response
    {
        $content = (string) $this->talkHandler
            ->grabTalk((int) $request->get('id'))
            ->setFavorite($request->get('delete') == null);

        return new Response($content);
    }

    /**
     * Set Selected Talk [POST]
     *
     * @param Request $request Request Object
     *
     * @return Response
     */
    public function selectAction(Request $request): Response
    {
        $content = (string) $this->talkHandler
            ->grabTalk((int) $request->get('id'))
            ->select($request->get('delete') != true);

        return new Response($content);
    }

    public function commentCreateAction(Request $request): Response
    {
        $talkId = (int) $request->get('id');

        $this->talkHandler
            ->grabTalk($talkId)
            ->commentOn($request->get('comment'));

        $request->getSession()->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => 'Comment Added!',
        ]);

        return new RedirectResponse($this->url('admin_talk_view', ['id' => $talkId]));
    }
}
