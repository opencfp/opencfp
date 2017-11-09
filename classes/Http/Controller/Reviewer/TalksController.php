<?php

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Http\Controller\BaseController;
use Pagerfanta\View\DefaultView;
use Symfony\Component\HttpFoundation\Request;

class TalksController extends BaseController
{
    public function indexAction(Request $req)
    {
        $reviewerId = $this->service(Authentication::class)->userId();

        $options = [
            'order_by' => $req->get('order_by'),
            'sort' => $req->get('sort'),
        ];

        $pager_formatted_talks = $this->service(TalkFilter::class)->getFilteredTalks(
            $req->get('filter'),
            $reviewerId,
            $options
        );

        $per_page = (int) $req->get('per_page') ?: 20;

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($pager_formatted_talks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($per_page);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        $queryParams = $req->query->all();
        // Create our default view for the navigation options
        $routeGenerator = function ($page) use ($queryParams) {
            $queryParams['page'] = $page;
            return '/reviewer/talks?' . http_build_query($queryParams);
        };
        $view = new DefaultView();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            ['proximity' => 3]
        );

        $templateData = [
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($pager_formatted_talks),
            'filter' => $req->get('filter'),
            'per_page' => $per_page,
            'sort' => $req->get('sort'),
            'order_by' => $req->get('order_by'),
        ];

        return $this->render('reviewer/talks/index.twig', $templateData);
    }

    public function viewAction()
    {
        //TODO: add function
    }

    public function rateAction(Request $req)
    {
        /** @var TalkRatingStrategy $talkRatingStrategy */
        $talkRatingStrategy = $this->service(TalkRatingStrategy::class);

        try {
            $talk_rating = (int) $req->get('rating');
            $talk_id = (int) $req->get('id');

            $talkRatingStrategy->rate($talk_id, $talk_rating);
        } catch (TalkRatingException $e) {
            return false;
        }
        return true;
    }
}
