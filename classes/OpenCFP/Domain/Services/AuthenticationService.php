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
     * Retrieves the currently authenticate user's username.
     *
     * @return string the unique username of the current authenticated user.
     *
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser();

    /**
     * Destroys the user's active authenticated session.
     *
     * @return void
     *
     * @throws NotAuthenticatedException
     */
    public function logout();

} 