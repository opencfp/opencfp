<?php

namespace OpenCFP;

use Silex\Application;
use OpenCFP\Talk;
use Symfony\Component\HttpFoundation\Request;

class DashboardController
{
    public function indexAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $talk = new Talk($app['db']);

        return $app['twig']->render('dashboard.twig', array(
            'myTalks' => $talk->findByUserId($user->getId()),
            'user'    => $app['sentry']->getUser(),
        ));
    }
}

