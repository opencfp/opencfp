<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;

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
        $speakers = $this->service('application.speakers');

        try {
            $profile = $speakers->findProfile();

            return $this->render('dashboard.twig', [
                'profile' => $profile,
                'cfp_open' => $this->isCfpOpen(),
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }
    }

    /**
     * Check to see if the CfP for this app is still open
     *
     * @param integer|\DateTimeInterface $currentTime
     *
     * @return boolean
     */
    public function isCfpOpen($currentTime = null)
    {
        if (! $currentTime) {
            $currentTime = new \Datetime();
        }

        if (! $currentTime instanceof \DateTimeInterface) {
            $currentTime = new \DateTime('@' . $currentTime);
        }

        if ($currentTime > new \DateTime($this->app->config('application.enddate') . ' 11:59 PM')) {
            return false;
        }

        return true;
    }
}
