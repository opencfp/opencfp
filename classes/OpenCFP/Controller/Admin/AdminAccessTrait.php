<?php
/**
 * Created by PhpStorm.
 * User: kayladnls
 * Date: 10/16/14
 * Time: 10:46 PM
 */

namespace OpenCFP\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

trait AdminAccessTrait
{
    public function __call($method,$arguments) {
        if(method_exists($this, $method)) {
            $app = $arguments[1];
            // Check if user is an logged in and an Admin
            if (!$this->userHasAccess($app)) {
                return $app->redirect($app['url'] . '/dashboard');
            }
            return call_user_func_array(array($this, $method), $arguments);
        }
    }

    protected function userHasAccess($app)
    {
        if (!$app['sentry']->check()) {
            return false;
        }

        $user = $app['sentry']->getUser();

        if (!$user->hasPermission('admin')) {
            return false;
        }

        return true;
    }
} 