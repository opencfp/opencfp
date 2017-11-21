<?php

namespace OpenCFP\Domain\Services\TalkRating;

interface TalkRatingStrategy
{
    public function isValidRating($rating): bool;

    /**
     * @param int $talkId
     * @param $rating
     *
     * @throws TalkRatingException
     */
    public function rate(int $talkId, $rating);
}
