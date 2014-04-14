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
        $talks = new Talk($app['db']);

        $template = $app['twig']->loadTemplate('admin/index.twig');
        $templateData = array(
            'speakerTotal' => $speakers->getTotalRecords(),
            'talkTotal' => $talks->getTotalRecords(),
            'favoriteTotal' => $talks->getTotalRecords('favorite', 1),
            'selectTotal' => $talks->getTotalRecords('selected', 1),
            'talks' => $talks->getRecent()
        );

        return $template->render($templateData);
    }
}


