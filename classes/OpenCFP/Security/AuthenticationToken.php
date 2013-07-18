<?php

namespace OpenCFP\Security;

use Cartalyst\Sentry\Users\UserInterface;

class AuthenticationToken implements AuthenticationTokenInterface
{
    private $authenticated;
    private $error;
    private $user;

    public function __construct($authenticated = false)
    {
        $this->authenticated = (bool) $authenticated;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setAuthenticationError($error)
    {
        $this->error = $error;
    }

    public function getAuthenticationError()
    {
        return $this->error;
    }

    public function setAuthenticated()
    {
        $this->authenticated = true;
        $this->error = null;
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }
}