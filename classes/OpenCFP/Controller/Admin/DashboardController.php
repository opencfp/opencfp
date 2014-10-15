<?php
namespace OpenCFP\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
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

        $user_mapper = $app['spot']->mapper('OpenCFP\Entity\User');
        $speaker_total = $user_mapper->all()->count();

        $talk_mapper = $app['spot']->mapper('OpenCFP\Entity\Talk');
        $favorite_mapper = $app['spot']->mapper('OpenCFP\Entity\Favorite');
        $recent_talks = $talk_mapper->getRecent($app['sentry']->getUser()->getId());

        $template = $app['twig']->loadTemplate('admin/index.twig');
        $templateData = array(
            'speakerTotal' => $speaker_total,
            'talkTotal' => $talk_mapper->all()->count(),
            'favoriteTotal' => $favorite_mapper->all()->count(),
            'selectTotal' => $talk_mapper->all()->where(['selected' => 1])->count(),
            'talks' => $recent_talks
        );

        return $template->render($templateData);
    }
}


