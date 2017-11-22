<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class SentinelIdentityProvider implements IdentityProvider
{
    private $sentinel;
    private $repo;

    public function __construct(Sentinel $sentinel, SpeakerRepository $repo)
    {
        $this->sentinel = $sentinel;
        $this->repo     = $repo;
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
        $user = $this->sentinel->getUser();
        if ($user == null || !$user) {
            throw new NotAuthenticatedException();
        }

        return $this->repo->findById($user->getUserId());
    }
}
