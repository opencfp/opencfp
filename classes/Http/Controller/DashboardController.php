<?php

namespace OpenCFP\Http\Controller;

use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Services\NotAuthenticatedException;

class DashboardController extends BaseController
{
    public function showSpeakerProfile()
    {
        $speakers = $this->service('application.speakers');
        try {
            $profile = $speakers->findProfile();
            /** @var CallForProposal $cfp */
            $cfp = $this->service('callforproposal');

            return $this->render('dashboard.twig', [
                'profile' => $profile,
                'cfp_open' => $cfp->isOpen(),
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }
    }
}
