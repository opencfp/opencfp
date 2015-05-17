<?php

namespace OpenCFP\Infrastructure\Auth; 

use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use Symfony\Component\HttpFoundation\Request;

class UhhhmIdentityProvider implements IdentityProvider
{

    /**
     * @var SpeakerRepository
     */
    private $repository;
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request, SpeakerRepository $repository)
    {
        $this->repository = $repository;
        $this->request = $request;
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
        return $this->repository->findById($this->request->get('auth'));
    }
}