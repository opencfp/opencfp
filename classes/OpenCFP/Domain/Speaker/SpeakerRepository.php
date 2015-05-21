<?php namespace OpenCFP\Domain\Speaker;

use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\EntityNotFoundException;

interface SpeakerRepository
{
    /**
     * Retrieves a speaker with associated talks.
     *
     * @param  string                  $speakerId
     * @throws EntityNotFoundException
     * @return User                    the speaker that matches given identifier.
     */
    public function findById($speakerId);

    /**
     * Saves a speaker and their talks.
     *
     * @param  User  $speaker
     * @return mixed
     */
    public function persist(User $speaker);
}
