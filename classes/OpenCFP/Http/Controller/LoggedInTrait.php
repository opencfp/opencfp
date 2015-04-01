<?php
/**
 * Created by PhpStorm.
 * User: kayladaniels
 * Date: 4/1/15
 * Time: 3:02 PM
 */

namespace OpenCFP\Http\Controller;


trait LoggedInTrait
{
    public function enforceUserIsLoggedIn()
    {
        if (!$this->app['sentry']->check()) {
            return $this->redirectTo('login');
        }
    }
}