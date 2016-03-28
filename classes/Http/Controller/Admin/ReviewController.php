<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Http\Controller\BaseController;
use Pagerfanta\View\TwitterBootstrap3View;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];

        $user = $sentry->getUser();

        /* @var Locator $spot */
        $spot = $this->app['spot'];
        
        // Get list of talks where majority of admins 'favorited' them
        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
        $options = [
            'order_by' => $req->get('order_by'),
            'sort' => $req->get('sort'),
        ];

        $per_page = (int) $req->get('per_page') ?: 20;
        $talks = $mapper->getTopRatedByUserId($user->getId(), $options);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($talks);
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
            return '/admin/review?' . http_build_query($queryParams);
        };

        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            ['proximity' => 3]
        );

        $template_data = [
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'totalRecords' => count($talks),
            'per_page' => $per_page,
            'filter' => $req->get('filter'),
            'sort' => $req->get('sort'),
            'order_by' => $req->get('order_by'),
        ];

        return $this->render('admin/review/index.twig', $template_data);
    }
}
