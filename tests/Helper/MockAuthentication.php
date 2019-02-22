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

namespace OpenCFP\Test\Helper;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Infrastructure\Auth\UserInterface;

final class MockAuthentication implements Authentication
{
    /**
     * @var Authentication
     */
    private $wrapped;

    /**
     * @var null|bool
     */
    private $isAuthenticated;

    /**
     * @var null|UserInterface
     */
    private $user;

    public function __construct(Authentication $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * Assume that the given user is logged in.
     *
     * @param UserInterface $user
     */
    public function overrideUser(UserInterface $user)
    {
        $this->isAuthenticated = true;
        $this->user            = $user;
    }

    /**
     * Assume that no user is authenticated.
     */
    public function overrideUnauthenticated()
    {
        $this->isAuthenticated = false;
        $this->user            = null;
    }

    /**
     * Switch back to the wrapped authenticator.
     */
    public function reset()
    {
        $this->isAuthenticated = $this->user = null;
    }

    public function authenticate($username, $password)
    {
        $this->wrapped->authenticate($username, $password);
    }

    public function user(): UserInterface
    {
        if ($this->isAuthenticated === false) {
            throw new NotAuthenticatedException();
        }

        return $this->user ?: $this->wrapped->user();
    }

    public function isAuthenticated(): bool
    {
        if ($this->isAuthenticated !== null) {
            return $this->isAuthenticated;
        }

        return $this->wrapped->isAuthenticated();
    }

    public function logout()
    {
        $this->reset();
        $this->wrapped->logout();
    }
}
