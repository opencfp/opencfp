<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Pagerfanta\View\TwitterBootstrap3View;
use Symfony\Component\HttpFoundation\Request;

class AdminsController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        $adminGroup = $this->app['sentry']->getGroupProvider()->findByName('Admin');
        $adminUsers = $this->app['sentry']->findAllUsersInGroup($adminGroup);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($adminUsers->toArray());
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/admin/admins?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            ['proximity' => 3]
        );

        $templateData = [
            'pagination' => $pagination,
            'speakers' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
        ];

        return $this->render('admin/admins/index.twig', $templateData);
    }

    public function removeAction(Request $req)
    {
        $admin = $this->app['sentry']->getUser();

        if ($admin->getId() == $req->get('id')) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, you cannot remove yourself as Admin.',
            ]);

            return $this->redirectTo('admin_admins');
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $user_data = $mapper->get($req->get('id'))->toArray();
        $user = $this->app['sentry']->getUserProvider()->findByLogin($user_data['email']);

        $adminGroup = $this->app['sentry']->getGroupProvider()->findByName('Admin');
        $response = $user->removeGroup($adminGroup);

        if ($response == true) {
            $this->app['session']->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => 'Successfully removed the Admin!',
            ]);
        }

        if ($response == false) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'We were unable to remove the Admin. Please try again.',
            ]);
        }

        return $this->redirectTo('admin_admins');
    }
}
