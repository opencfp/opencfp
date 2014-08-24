<?php
namespace OpenCFP\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Model\Talk;
use OpenCFP\Model\Speaker;
use Pagerfanta\View\TwitterBootstrap3View;

class DashboardController
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

        $speakers = new Speaker($app['db']);
        $mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $favorite_mapper = $app['spot']->mapper('OpenCFP\Entity\Favorite');
        $recent_talks = $mapper->getRecent($app['sentry']->getUser()->getId());

        $template = $app['twig']->loadTemplate('admin/index.twig');
        $templateData = array(
            'speakerTotal' => $speakers->getTotalRecords(),
            'talkTotal' => $mapper->all()->count(),
            'favoriteTotal' => $favorite_mapper->all()->count(),
            'selectTotal' => $mapper->all()->where(['selected' => 1])->count(),
            'talks' => $recent_talks
        );

        return $template->render($templateData);
    }
}


