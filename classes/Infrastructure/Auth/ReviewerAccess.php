<?php

namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\UserAccess;
use Silex\Application;

class ReviewerAccess implements UserAccess
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

        if (!$user->hasPermission('reviewer')) {
            return $app->redirect('/dashboard');
        }
    }
}
