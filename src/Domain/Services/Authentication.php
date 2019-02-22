<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

interface Authentication
{
    /**
     * Given valid credentials, authenticate the user.
     *
     * @param string $username
     * @param string $password
     *
     * @throws AuthenticationException
     * @throws UserNotFoundException
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
     * Determines whether or not the user is logged in.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Destroys the user's active authenticated session.
     */
    public function logout();
}
