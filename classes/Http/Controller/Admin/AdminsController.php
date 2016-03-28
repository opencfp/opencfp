<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Http\Controller\BaseController;
use Pagerfanta\View\TwitterBootstrap3View;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class AdminsController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        
        $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
        $adminUsers = $sentry->findAllUsersInGroup($adminGroup);

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
        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        
        $admin = $sentry->getUser();

        if ($admin->getId() == $req->get('id')) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Sorry, you cannot remove yourself as Admin.',
            ]);

            return $this->redirectTo('admin_admins');
        }

        /* @var Locator $spot */
        $spot = $this->app['spot'];
        
        $mapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
        $user_data = $mapper->get($req->get('id'))->toArray();
        $user = $sentry->getUserProvider()->findByLogin($user_data['email']);

        $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
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
