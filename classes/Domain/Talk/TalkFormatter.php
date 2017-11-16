<?php

namespace OpenCFP\Domain\Talk;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Services\TalkFormat;

class TalkFormatter implements TalkFormat
{
    /**
     * Iterates over a collection of DBAL objects and returns a formatted result set
     *
     * @param Collection $talkCollection Collection of Talks
     * @param int        $admin_user_id
     * @param bool       $userData
     *
     * @return Collection
     */
    public function formatList(Collection $talkCollection, int $admin_user_id, bool $userData = true): Collection
    {
        return $talkCollection
            ->map(function ($talk) use ($admin_user_id, $userData) {
                return new TalkProfile($talk, $admin_user_id);
            });
    }

    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param mixed $talk
     * @param int   $admin_user_id
     * @param bool  $userData      grab the speaker data or not
     *
     * @return TalkProfile
     */
    public function createdFormattedOutput($talk, int $admin_user_id, bool $userData = true)
    {
        return new TalkProfile($talk, $admin_user_id);
    }
}
