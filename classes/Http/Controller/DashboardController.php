<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForPapers;
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

            /** @var CallForPapers $cfp */
            $cfp = $this->service('callforpapers');

            return $this->render('dashboard.twig', [
                'profile'  => $profile,
                'cfp_open' => $cfp->isOpen(),
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }
    }
}
