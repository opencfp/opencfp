<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;

class SentryIdentityProvider implements IdentityProvider
{
    private $sentry;

    public function __construct(Sentry $sentry){
        $this->sentry = $sentry;
    }

    /**
     * Retrieves the currently authenticate user's username.
     *
     * @return User
     *
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser()
    {
        return $this->sentry->getUser();
    }
}