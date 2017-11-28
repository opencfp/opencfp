<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Auth\Contracts\IdentityProvider;

class SentryIdentityProvider implements IdentityProvider
{
    private $sentry;
    private $speakerRepository;

    public function __construct(Sentry $sentry, SpeakerRepository $mapper)
    {
        $this->sentry            = $sentry;
        $this->speakerRepository = $mapper;
    }

    /**
     * Retrieves the currently authenticate user's username.
     *
     * @throws NotAuthenticatedException
     *
     * @return User
     */
    public function getCurrentUser()
    {
        $sentryUser = $this->sentry->getUser();

        if (!$sentryUser) {
            throw new NotAuthenticatedException();
        }

        return $this->speakerRepository->findById($sentryUser->getId());
    }
}
