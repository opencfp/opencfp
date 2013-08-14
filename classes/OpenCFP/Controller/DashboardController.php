<?php
namespace OpenCFP\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use OpenCFP\Model\Talk;

class DashboardController
{
    public function indexAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect($app['url'] . '/login');
        }

        $user = $app['sentry']->getUser();
        $permissions = $user->getPermissions();
        $talk = new Talk($app['db']);
        $my_talks = $talk->findByUserId($user->getId());
        
        // Load our template and RENDER
        $template = $app['twig']->loadTemplate('dashboard.twig');
        $template_data = array(
            'myTalks' => $my_talks,
            'user' => $user,
            'permissions' => $permissions,
        );

        return $template->render($template_data);
    }
}

