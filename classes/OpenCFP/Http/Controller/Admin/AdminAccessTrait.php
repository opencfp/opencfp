<?php

namespace OpenCFP\Http\Controller\Admin;

trait AdminAccessTrait
{
    public function __call($method,$arguments)
    {
        if (method_exists($this, $method)) {
            // Check if user is an logged in and an Admin
            if ( ! $this->userHasAccess($this->app)) {
                return $this->redirectTo('dashboard');
            }

            return call_user_func_array(array($this, $method), $arguments);
        }
    }

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
