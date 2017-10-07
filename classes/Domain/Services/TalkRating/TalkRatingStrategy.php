<?php


namespace OpenCFP\Domain\Services\TalkRating;

interface TalkRatingStrategy
{
    public function isValidRating($rating): bool;

    public function rate(int $talkId, $rating);
}
