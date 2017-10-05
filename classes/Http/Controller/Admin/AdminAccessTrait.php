<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Services\Authentication;

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
        /** @var Authentication $auth */
        $auth = $this->app[Authentication::class];

        if (!$auth->check()) {
            return false;
        }

        $user = $auth->user();

        if (!$user->hasPermission('admin')) {
            return false;
        }

        return true;
    }
}
