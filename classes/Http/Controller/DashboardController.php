<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
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

            /** @var CallForProposal $cfp */
            $cfp = $this->service('callforproposal');

            return $this->render('dashboard.twig', [
                'profile'  => $profile,
                'cfp_open' => $cfp->isOpen(),
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }
    }
}
