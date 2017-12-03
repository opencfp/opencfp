<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use OpenCFP\Infrastructure\Auth\UserInterface;

interface Authentication
{
    /**
     * Given valid credentials, authenticate the user.
     *
     * @param string $username
     * @param string $password
     *
     * @throws AuthenticationException
     */
    public function authenticate($username, $password);

    /**
     * Returns current authenticated User account.
     *
     * @throws NotAuthenticatedException
     *
     * @return UserInterface
     */
    public function user(): UserInterface;

    /**
     * Returns current authenticated User Id.
     *
     * @throws NotAuthenticatedException
     *
     * @return int
     */
    public function userId(): int;

    /**
     * Determines whether or not the user is logged in.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Determine whether the user is a non-authenticated guest.
     *
     * @return bool
     */
    public function guest(): bool;

    /**
     * Destroys the user's active authenticated session.
     */
    public function logout();
}
