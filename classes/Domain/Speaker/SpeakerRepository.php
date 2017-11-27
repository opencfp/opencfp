<?php

namespace OpenCFP\Domain\Speaker;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\User;

interface SpeakerRepository
{
    /**
     * Retrieves a speaker with associated talks.
     *
     * @param string $speakerId
     *
     * @throws EntityNotFoundException
     *
     * @return User the speaker that matches given identifier
     */
    public function findById($speakerId): User;

    /**
     * Saves a speaker and their talks.
     *
     * @param $speaker
     */
    public function persist($speaker);
}
