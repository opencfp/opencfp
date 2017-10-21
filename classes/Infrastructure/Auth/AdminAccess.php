<?php

namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\UserAccess;
use Silex\Application;

class AdminAccess implements UserAccess
{
    /**
     * {@inheritdoc}
     */
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
