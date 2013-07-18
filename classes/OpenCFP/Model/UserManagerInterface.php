<?php

namespace OpenCFP\Model;

interface UserManagerInterface
{
    /**
     * Create a new Sentry user.
     *
     * @param array $data
     * @return \Cartalyst\Sentry\Users\Eloquent\User
     */
    public function createUser(array $data);

    /**
     * Get a Sentry group.
     *
     * @param string $name The Sentry group name
     * @return \Cartalyst\Sentry\Groups\GroupInterface
     */
    public function getGroup($name);
}