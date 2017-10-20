<?php

namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Services\Authentication;
use Silex\Application;

class AdminAccess
{
    public static function userHasAccess(Application $app)
    {
        /** @var Authentication $auth */
        $auth = $app[Authentication::class];

        if (!$auth->check()) {
            return $app->redirect('/dashboard');
        }

        $user = $auth->user();

        if (!$user->hasPermission('admin')) {
            return $app->redirect('/dashboard');
        }
    }
}
