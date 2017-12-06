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

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class TalksController extends BaseController
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var TalkFilter
     */
    private $talkFilter;

    /**
     * @var TalkHandler
     */
    private $talkHandler;

    public function __construct(
        Authentication $authentication,
        TalkFilter $talkFilter,
        TalkHandler $talkHandler,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->talkFilter     = $talkFilter;
        $this->talkHandler    = $talkHandler;

        parent::__construct($twig, $urlGenerator);
    }

    public function indexAction(Request $request)
    {
        $adminUserId = $this->authentication->user()->getId();
        $options     = [
            'order_by' => $request->get('order_by'),
            'sort'     => $request->get('sort'),
        ];

        $formattedTalks = $this->talkFilter->getTalks(
            $adminUserId,
            $request->get('filter'),
            $options
        );

        // Set up our page stuff
        $perPage    = (int) $request->get('per_page') ?: 20;
        $pagerfanta = new Pagination($formattedTalks, $perPage);

        $pagerfanta->setCurrentPage($request->get('page'));
        $pagination = $pagerfanta->createView('/admin/talks?', $request->query->all());

        $templateData = [
            'pagination'   => $pagination,
            'talks'        => $pagerfanta->getFanta(),
            'page'         => $pagerfanta->getCurrentPage(),
            'current_page' => $request->getRequestUri(),
            'totalRecords' => \count($formattedTalks),
            'filter'       => $request->get('filter'),
            'per_page'     => $perPage,
            'sort'         => $request->get('sort'),
            'order_by'     => $request->get('order_by'),
        ];

        return $this->render('admin/talks/index.twig', $templateData);
    }

    public function viewAction(Request $request)
    {
        $handler = $this->talkHandler
            ->grabTalk((int) $request->get('id'));

        if (!$handler->view()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested talk',
            ]);

            return $this->redirectTo('admin_talks');
        }

        return $this->render('admin/talks/view.twig', ['talk' => $handler->getProfile()]);
    }

    public function rateAction(Request $request)
    {
        try {
            $this->validate($request, [
                'rating' => 'required|integer',
            ]);

            $content = (string) $this->talkHandler
                ->grabTalk((int) $request->get('id'))
                ->rate((int) $request->get('rating'));
        } catch (ValidationException $e) {
            $content = '';
        }

        return new Response($content);
    }

    /**
     * Set Favorited Talk [POST]
     *
     * @param Request $request Request Object
     *
     * @return Response
     */
    public function favoriteAction(Request $request)
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
    public function selectAction(Request $request)
    {
        $content = (string) $this->talkHandler
            ->grabTalk((int) $request->get('id'))
            ->select($request->get('delete') != true);

        return new Response($content);
    }

    public function commentCreateAction(Request $request)
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
