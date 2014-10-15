<?php
namespace OpenCFP\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;

class ReviewController
{
    protected function userHasAccess($app)
    {
        if (!$app['sentry']->check()) {
            return false;
        }

        $user = $app['sentry']->getUser();

        if (!$user->hasPermission('admin')) {
            return false;
        }

        return true;
    }

    public function indexAction(Request $req, Application $app)
    {
        // Check if user is an logged in and an Admin
        if (!$this->userHasAccess($app)) {
            return $app->redirect($app['url'] . '/dashboard');
        }

        $user = $app['sentry']->getUser();

        // How many admins make for a majority?
        $mapper = $app['spot']->mapper('OpenCFP\Entity\User');
        $admin_count = $mapper->all()
            ->where(['permissions' => '{"admin":1}'])
            ->count();
        $admin_majority = (int)($admin_count * .501) + 1;

        // Get list of talks where majority of admins 'favorited' them
        $mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
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
        $routeGenerator = function($page) {
            return '/admin/review?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $template = $app['twig']->loadTemplate('admin/review/index.twig');
        $template_data = [
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'totalRecords' => count($favorite_talks)
        ];

        return $template->render($template_data);
    }
}


