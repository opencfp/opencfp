<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{
    public function showSpeakerProfile()
    {
        /**
         * Local reference to speakers application service.
         *
         * This should be injected instead of using service location but there's currently a
         * "conflict" between Controller as Services and our custom ControllerResolver that injects the Application
         * container.
         *
         * @var Speakers $speakers
         */
        $speakers = $this->app['application.speakers'];

        /////////
        if (!$this->app['sentry']->check()) {
            return $this->redirectTo('login');
        }

        $user = $this->app['sentry']->getUser();
        /////////

        $profile = $speakers->findProfile($user->getId());

        return $this->render('dashboard.twig', [
            'profile' => $profile
        ]);
    }
}
