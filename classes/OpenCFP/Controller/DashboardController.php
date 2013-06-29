<?php

namespace OpenCFP\Controller;

use Silex\Application;
use OpenCFP\Model\Talk;
use Symfony\Component\HttpFoundation\Request;

class DashboardController
{
    public function indexAction(Application $app)
    {
        if (!$app['sentry']->check()) {
            return $app->redirect('/login');
        }

        $user = $app['sentry']->getUser();

        $talk = new Talk($app['db']);

        return $app['twig']->render('dashboard.twig', array(
            'talks' => $talk->findByUserId($user->getId()),
        ));
    }
}

