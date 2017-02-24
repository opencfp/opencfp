<?php
/**
 * This is a wrapper for Sentinel methods so it makes it easier to test
 */
namespace OpenCFP\Util\Wrapper;

use Cartalyst\Sentinel\Native\Facades\Sentinel;

class SentinelWrapper
{
    /**
     * @return mixed
     */
    public function check()
    {
        return Sentinel::check();
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function findRoleBySlug($slug)
    {
        return Sentinel::findRoleBySlug($slug);
    }

    /**
     * @param $user
     * @return mixed
     */
    public function registerAndActivate($user)
    {
        return Sentinel::registerAndActivate($user);
    }
}
