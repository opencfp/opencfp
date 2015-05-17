<?php

namespace OpenCFP\Infrastructure\Auth; 

use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;

class UhhhmIdentityProvider implements IdentityProvider
{

    /**
     * Retrieves the currently authenticate user's username.
     *
     * @return User
     *
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser()
    {

    }
}