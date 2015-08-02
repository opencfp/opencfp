<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use Silex\Application;

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

        try {
            $profile = $speakers->findProfile();

            return $this->render('dashboard.twig', [
                'profile' => $profile,
                'cfp_open' => $this->isCfpOpen()
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }

    }

    /**
     * Check to see if the CfP for this app is still open
     *
     * @param integer $currentTime
     *
     * @return boolean
     */
    public function isCfpOpen($currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = strtotime('now');
        }

        if ($currentTime < strtotime($this->app->config('application.enddate') . ' 11:59 PM')) {
            return true;
        }

        return false;
    }
}
