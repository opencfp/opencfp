<?php

namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\UserAccess;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SpeakerAccess implements UserAccess
{

    /**
     * If a user doesn't have access to a page they get redirected, otherwise nothing happens
     *
     * @param Application $app
     *
     * @return RedirectResponse|void
     */
    public static function userHasAccess(Application $app)
    {
        /** @var Authentication $auth */
        $auth = $app[Authentication::class];

        if (!$auth->check()) {
            return $app->redirect('/login');
        }
    }
}
