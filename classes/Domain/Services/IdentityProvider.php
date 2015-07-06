<?php

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\Entity\User;

interface IdentityProvider
{
    /**
     * Retrieves the currently authenticate user's username.
     *
     * @return User
     *
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser();

}
