<?php

namespace OpenCFP\Domain\Services;

interface AuthenticationService
{
    /**
     * Given valid credentials, authenticate the user.
     *
     * @param string $username
     * @param string $password
     *
     * @throws InvalidCredentialsException
     */
    public function authenticate($username, $password);

    /**
     * Destroys the user's active authenticated session.
     *
     * @return void
     *
     * @throws NotAuthenticatedException
     */
    public function logout();

} 