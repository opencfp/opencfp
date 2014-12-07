<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;

class ReviewController extends BaseController
{
    use AdminAccessTrait;

    private function indexAction(Request $req)
    {
        $user = $this->app['sentry']->getUser();

        // Get list of talks where majority of admins 'favorited' them
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $mapper->getTopRatedTalksByUserId($user->id);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($talks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/admin/review?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $template_data = [
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'totalRecords' => count($talks)
        ];

        return $this->render('admin/review/index.twig', $template_data);
    }
}
