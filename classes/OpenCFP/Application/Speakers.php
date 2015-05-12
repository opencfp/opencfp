<?php

namespace OpenCFP\Application;

use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Domain\Talk\TalkSubmission;

final class Speakers
{
    /**
     * @var IdentityProvider
     */
    protected $identityProvider;

    /** @var SpeakerRepository */
    protected $speakers;

    /**
     * @var TalkRepository
     */
    protected $talks;

    function __construct(IdentityProvider $identityProvider, SpeakerRepository $speakers, TalkRepository $talks)
    {
        $this->speakers = $speakers;
        $this->identityProvider = $identityProvider;
        $this->talks = $talks;
    }

    /**
     * Retrieves the speaker profile from their speaker identifier.
     *
     * @param string $speakerId
     * @return SpeakerProfile
     */
    public function findProfile($speakerId)
    {
        $speaker = $this->speakers->findById($speakerId);
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
        $speaker = $this->speakers->findById($speakerId);
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

    /**
     * Orchestrates the use-case of a speaker submitting a talk.
     *
     * @param TalkSubmission $submission
     */
    public function submitTalk(TalkSubmission $submission)
    {
        $speaker = $this->identityProvider->getCurrentUser();

        $talk = new Talk([
            'title' => 'Sample Talk',
            'description' => 'Some example talk for our submission'
        ]);

        // Own the talk to the speaker.
        $talk->user_id = $speaker->id;

        $this->talks->persist($talk);
    }
}
