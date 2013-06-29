<?php

namespace OpenCFP\Security;

interface AuthenticationTokenInterface
{
    /**
     * Returns whether or not the token is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Flags the token authenticated.
     *
     * @return void
     */
    public function setAuthenticated();

    /**
     * Returns the UserInterface implementation.
     *
     * @return \Cartalyst\Sentry\Users\UserInterface
     */
    public function getUser();
}