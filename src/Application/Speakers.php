<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Application;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerProfile;

class Speakers
{
    /** @var IdentityProvider */
    private $identityProvider;

    public function __construct(IdentityProvider $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * Retrieves the speaker profile for currently authenticated speaker.
     *
     * @return SpeakerProfile
     */
    public function findProfile(): SpeakerProfile
    {
        $speaker = $this->identityProvider->getCurrentUser();

        return new SpeakerProfile($speaker);
    }

    /**
     * Retrieves a talk owned by a speaker.
     *
     * @param int $talkId
     *
     * @throws NotAuthorizedException
     *
     * @return Talk
     */
    public function getTalk(int $talkId)
    {
        $speaker = $this->identityProvider->getCurrentUser();
        $talk    = $speaker->talks()->find($talkId);

        // If it can't grab by relation, it's likely not their talk.
        if (!$talk instanceof Talk) {
            throw new NotAuthorizedException();
        }

        // Do an explicit check of ownership because why not.
        if ((int) $talk->user_id !== (int) $speaker->id) {
            throw new NotAuthorizedException();
        }

        return $talk;
    }

    public function getTalks()
    {
        $speaker = $this->identityProvider->getCurrentUser();

        return $speaker->talks;
    }
}
