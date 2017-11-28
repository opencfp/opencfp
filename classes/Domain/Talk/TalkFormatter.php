<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Talk;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Services\TalkFormat;

class TalkFormatter implements TalkFormat
{
    /**
     * Iterates over a collection of DBAL objects and returns a formatted result set
     *
     * @param Collection $talkCollection Collection of Talks
     * @param int        $adminUserId
     * @param bool       $userData
     *
     * @return Collection
     */
    public function formatList(Collection $talkCollection, int $adminUserId, bool $userData = true): Collection
    {
        return $talkCollection
            ->map(function ($talk) use ($adminUserId, $userData) {
                return new TalkProfile($talk, $adminUserId);
            });
    }

    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param mixed $talk
     * @param int   $adminUserId
     * @param bool  $userData    grab the speaker data or not
     *
     * @return TalkProfile
     */
    public function createdFormattedOutput($talk, int $adminUserId, bool $userData = true)
    {
        return new TalkProfile($talk, $adminUserId);
    }
}
