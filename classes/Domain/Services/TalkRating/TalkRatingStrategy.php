<?php

namespace OpenCFP\Domain\Services\TalkRating;

interface TalkRatingStrategy
{
    public function isValidRating(int $rating): bool;

    /**
     * @throws TalkRatingException
     */
    public function rate(int $talkId, int $rating);
}
