<?php

namespace OpenCFP\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class IlluminateSpeakerRepository implements SpeakerRepository
{
    /**
     * @var User
     */
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Retrieves a speaker with associated talks.
     *
     * @param string $speakerId
     *
     * @throws EntityNotFoundException
     *
     * @return User the speaker that matches given identifier
     */
    public function findById($speakerId): User
    {
        try {
            $speaker = $this->userModel->findOrFail($speakerId);
        } catch (ModelNotFoundException $e) {
            throw new EntityNotFoundException();
        }

        return $speaker;
    }

    /**
     * Saves a speaker and their talks.
     *
     * @param  $speaker
     */
    public function persist($speaker)
    {
        $speaker->save();
    }
}
