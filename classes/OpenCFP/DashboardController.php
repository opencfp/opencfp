<?php
namespace OpenCFP;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController
{
    public function indexAction(Request $req, Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();
        $talk = new \OpenCFP\Talk($app['db']);
        $myTalks = $talk->findByUserId($user->getId());

        // Load our template and RENDER
        $template = $app['twig']->loadTemplate('dashboard.twig');
        $templateData = array(
            'myTalks' => $myTalks,
            'user' => $user,
        );

        return $template->render($templateData);
    }
}

