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
     * @param array $credentials
     * @return mixed
     */
    public function findByCredentials(Array $credentials)
    {
        return Sentinel::findByCredentials($credentials);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function findRoleBySlug($slug)
    {
        return Sentinel::findRoleBySlug($slug);
    }

    public function login($user)
    {
        return Sentinel::login($user);
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return Sentinel::getUser();
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
