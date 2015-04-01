<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends BaseController
{
    use LoggedInTrait;

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
        $this->enforceUserIsLoggedIn();

        $user = $this->app['sentry']->getUser();
        /////////

        $profile = $speakers->findProfile($user->getId());

        return $this->render('dashboard.twig', [
            'profile' => $profile,
            'cfp_open' => $this->isCfpOpen()
        ]);
    }
}
