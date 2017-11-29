<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

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
     * @param User $speaker
     */
    public function persist(User $speaker);
}
