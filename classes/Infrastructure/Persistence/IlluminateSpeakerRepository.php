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

    public function findById($speakerId): User
    {
        try {
            $speaker = $this->userModel->findOrFail($speakerId);
        } catch (ModelNotFoundException $e) {
            throw new EntityNotFoundException();
        }

        return $speaker;
    }

    public function persist(User $speaker)
    {
        $speaker->save();
    }
}
