<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class SentryIdentityProvider implements IdentityProvider
{
    private $sentry;
    private $speakerRepository;

    public function __construct(Sentry $sentry, SpeakerRepository $mapper)
    {
        $this->sentry = $sentry;
        $this->speakerRepository = $mapper;
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
        $sentryUser = $this->sentry->getUser();

        if (!$sentryUser) {
            throw new NotAuthenticatedException();
        }

        return $this->speakerRepository->findById($sentryUser->getId());
    }
}
