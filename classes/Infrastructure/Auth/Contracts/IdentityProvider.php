<?php

namespace OpenCFP\Infrastructure\Auth\Contracts;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\NotAuthenticatedException;

interface IdentityProvider
{
    /**
     * Retrieves the currently authenticate user's username.
     *
     * @throws NotAuthenticatedException
     *
     * @return User
     */
    public function getCurrentUser();
}
