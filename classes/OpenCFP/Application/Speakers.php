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

    /**
     * Retrieves a talk owned by a speaker.
     *
     * @param $speakerId
     * @param $talkId
     *
     * @return Talk
     * @throws NotAuthorizedException
     */
    public function getTalk($speakerId, $talkId)
    {
        $speaker = $this->speakerRepository->findById($speakerId);
        $talk = $speaker->talks->where(['id' => $talkId])->execute()->first();

        // If it can't grab by relation, it's likely not their talk.
        if (!$talk) {
            throw new NotAuthorizedException;
        }

        // Do an explicit check of ownership because why not.
        if ($talk->user_id !== $speaker->id) {
            throw new NotAuthorizedException;
        }

        return $talk;
    }
}
