<?php

namespace OpenCFP\Infrastructure\Auth;

use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Exception\InvalidRequestException;
use League\OAuth2\Server\ResourceServer;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class OAuthIdentityProvider implements IdentityProvider
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * @var SpeakerRepository
     */
    private $speakerRepository;

    public function __construct(ResourceServer $server, SpeakerRepository $speakerRepository)
    {
        $this->server            = $server;
        $this->speakerRepository = $speakerRepository;
    }

    /**
     * Retrieves the currently authenticate user's username.
     *
     * @return User
     *
     * @throws InvalidRequestException
     * @throws AccessDeniedException
     */
    public function getCurrentUser()
    {
        $this->server->isValidRequest();

        // Choooo chooo!!
        $ownerId = $this->server->getAccessToken()->getSession()->getOwnerId();

        return $this->speakerRepository->findById($ownerId);
    }
}
