<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;

class ReviewController extends BaseController
{
    use AdminAccessTrait;

    private function indexAction(Request $req)
    {
        $user = $this->app['sentry']->getUser();

        // How many admins make for a majority?
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $admin_count = $mapper->all()
            ->where(['permissions' => '{"admin":1}'])
            ->count();
        $admin_majority = (int) ($admin_count * .501) + 1;

        // Get list of talks where majority of admins 'favorited' them
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $favorite_talks = $mapper->getAdminFavorites($user->id, $admin_majority);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($favorite_talks);
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
            'totalRecords' => count($favorite_talks)
        ];

        return $this->render('admin/review/index.twig', $template_data);
    }
}
