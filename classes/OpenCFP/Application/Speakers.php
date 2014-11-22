<?php

namespace OpenCFP\Application;

use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Speaker\SpeakerRepository;

final class Speakers
{
    /** @var SpeakerRepository */
    protected $speakerRepository;

    function __construct(SpeakerRepository $speakerRepository)
    {
        $this->speakerRepository = $speakerRepository;
    }

    /**
     * Retrieves the speaker profile from their speaker identifier.
     *
     * @param string $speakerId
     * @return SpeakerProfile
     */
    public function findProfile($speakerId)
    {
        $speaker = $this->speakerRepository->findById($speakerId);
        return new SpeakerProfile($speaker);
    }
}
