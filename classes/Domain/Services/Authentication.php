<?php

namespace OpenCFP\Domain\Services;

use Cartalyst\Sentry\Users\UserInterface;

interface Authentication
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
     * Returns current authenticated User account.
     *
     * @return UserInterface
     *
     * @throws NotAuthenticatedException
     */
    public function user(): UserInterface;

    /**
     * Returns current authenticated User Id.
     *
     * @return int
     *
     * @throws NotAuthenticatedException
     */
    public function userId(): int;

    /**
     * Determines whether or not the user is logged in.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Determine whether the user is a non-authenticated guest.
     *
     * @return bool
     */
    public function guest(): bool;

    /**
     * Destroys the user's active authenticated session.
     *
     * @return void
     */
    public function logout();
}
