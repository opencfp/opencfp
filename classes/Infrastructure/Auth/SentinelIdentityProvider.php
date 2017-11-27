<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface as SentinelUserInterface;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;

final class SentinelIdentityProvider implements IdentityProvider
{
    /**
     * @var Sentinel
     */
    private $sentinel;

    /**
     * @var SpeakerRepository
     */
    private $repo;

    public function __construct(Sentinel $sentinel, SpeakerRepository $repo)
    {
        $this->sentinel = $sentinel;
        $this->repo     = $repo;
    }

    /**
     * Retrieves the currently authenticated user
     *
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser(): User
    {
        $user = $this->sentinel->getUser();
        if (!$user instanceof SentinelUserInterface) {
            throw new NotAuthenticatedException();
        }

        return $this->repo->findById($user->getUserId());
    }
}
