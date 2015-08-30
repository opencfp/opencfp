<?php

namespace OpenCFP\Http\Controller\Admin;

trait AdminAccessTrait
{
    protected function userHasAccess($app)
    {
        if (!$this->app['sentry']->check()) {
            return false;
        }

        $user = $this->app['sentry']->getUser();

        if (!$user->hasPermission('admin')) {
            return false;
        }

        return true;
    }
}
