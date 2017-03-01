<?php
namespace OpenCFP\Infrastructure\Auth;

use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Util\Wrapper\SentinelWrapper;

class SentinelIdentityProvider implements IdentityProvider
{
    private $sentinel;
    private $speakerRepository;

    public function __construct(SentinelWrapper $sentinel, SpeakerRepository $mapper)
    {
        $this->sentinel = $sentinel;
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
        $sentinelUser = $this->sentinel->getUser();

        if (!$sentinelUser) {
            throw new NotAuthenticatedException();
        }

        return $this->speakerRepository->findById($sentinelUser['id']);
    }
}
