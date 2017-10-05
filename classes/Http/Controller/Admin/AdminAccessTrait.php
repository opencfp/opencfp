<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;

trait AdminAccessTrait
{
    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            // Check if user is an logged in and an Admin
            if (! $this->userHasAccess()) {
                return $this->redirectTo('dashboard');
            }

            return call_user_func_array([$this, $method], $arguments);
        }
    }

    protected function userHasAccess()
    {
        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        // TODO IdentityProvider and Authentication
        if (!$sentry->check()) {
            return false;
        }

        $user = $sentry->getUser();

        if (!$user->hasPermission('admin')) {
            return false;
        }

        return true;
    }
}
