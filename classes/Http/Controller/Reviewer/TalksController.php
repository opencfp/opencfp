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

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class TalksController extends BaseController
{
    public function indexAction(Request $req)
    {
        $reviewerId = $this->service(Authentication::class)->userId();

        $options = [
            'order_by' => $req->get('order_by'),
            'sort'     => $req->get('sort'),
        ];

        $formattedTalks = $this->service(TalkFilter::class)->getTalks(
            $reviewerId,
            $req->get('filter'),
            $options
        );

        $perPage    = (int) $req->get('per_page') ?: 20;
        $pagerfanta = new Pagination($formattedTalks, $perPage);
        $pagerfanta->setCurrentPage($req->get('page'));
        $pagination = $pagerfanta->createView('/reviewer/talks?', $req->query->all());

        $templateData = [
            'pagination'   => $pagination,
            'talks'        => $pagerfanta->getFanta(),
            'ratingSystem' => $this->service(TalkRatingStrategy::class)->getRatingName(),
            'page'         => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => \count($formattedTalks),
            'filter'       => $req->get('filter'),
            'per_page'     => $perPage,
            'sort'         => $req->get('sort'),
            'order_by'     => $req->get('order_by'),
        ];

        return $this->render('reviewer/talks/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        /** @var TalkHandler $handler */
        $handler = $this->service(TalkHandler::class)
            ->grabTalk((int) $req->get('id'));
        if (!$handler->view()) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested talk',
            ]);

            return $this->app->redirect($this->url('admin_talks'));
        }

        return $this->render('reviewer/talks/view.twig', [
            'ratingSystem' => $this->service(TalkRatingStrategy::class)->getRatingName(),
            'talk'         => $handler->getProfile(),
        ]);
    }

    public function rateAction(Request $req)
    {
        try {
            $this->validate([
                'rating' => 'required|integer',
            ]);

            return $this->service(TalkHandler::class)
                ->grabTalk((int) $req->get('id'))
                ->rate((int) $req->get('rating'));
        } catch (ValidationException $e) {
            return false;
        }
    }
}
