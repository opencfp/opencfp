<?php
/**
 * This is a wrapper for Sentinel methods so it makes it easier to test
 */
namespace OpenCFP\Util\Wrapper;

use Cartalyst\Sentinel\Native\Facades\Sentinel;

class SentinelWrapper
{
    public function __construct($environment)
    {
        /**
         * By default, Sentinel has two checkpoints enabled for authentication -- activation and throttle. While in
         * development mode, we will turn off the throttle checkpoint or else you won't be able to repeatedly test
         * anything to do with logins
         */
        if ($environment == 'development') {
            Sentinel::removeCheckpoint('throttle');
        }
    }

    /**
     * @param array $credentials
     * @return mixed
     */
    public function authenticate(array $credentials)
    {
        return Sentinel::authenticate($credentials);
    }

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
    public function findByCredentials(array $credentials)
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

    /**
     * @param $user
     * @return mixed
     */
    public function login($user)
    {
        return Sentinel::login($user);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        return Sentinel::logout(null, true);
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

    /**
     * @param $user
     * @param array $credentials
     * @return mixed
     */
    public function update($user, array $credentials)
    {
        return Sentinel::update($user, $credentials);
    }
}
