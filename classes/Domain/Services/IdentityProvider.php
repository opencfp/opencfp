<?php

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\Model\User;

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
