<?php

namespace OpenCFP\Application;

use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Domain\Talk\TalkSubmission;
use OpenCFP\Domain\ValidationException;

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

    /**
     * @var CallForProposal
     */
    protected $callForProposal;

    function __construct(CallForProposal $callForProposal, IdentityProvider $identityProvider, SpeakerRepository $speakers, TalkRepository $talks)
    {
        $this->speakers = $speakers;
        $this->identityProvider = $identityProvider;
        $this->talks = $talks;
        $this->callForProposal = $callForProposal;
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
     * @param $talkId
     *
     * @return Talk
     * @throws NotAuthorizedException
     */
    public function getTalk($talkId)
    {
        $speaker = $this->identityProvider->getCurrentUser();
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
     *
     * @throws \Exception
     */
    public function submitTalk(TalkSubmission $submission)
    {
        if (!$this->callForProposal->isOpen()) {
            throw new \Exception('You cannot create talks once the call for papers has ended.');
        }

        $user = $this->identityProvider->getCurrentUser();

        // Create talk from submission.
        $talk = $submission->toTalk();

        // Own the talk to the speaker.
        $talk->user_id = $user->id;

        $this->talks->persist($talk);
    }
}
