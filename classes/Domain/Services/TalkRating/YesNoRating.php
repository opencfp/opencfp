<?php

namespace OpenCFP\Domain\Services\TalkRating;

class YesNoRating extends TalkRating
{
    public function isValidRating($rating): bool
    {
        if ($rating < -1 || $rating > 1) {
            return false;
        }

        return true;
    }

    public function rate(int $talkId, $rating)
    {
        if (!$this->isValidRating($rating)) {
            throw TalkRatingException::invalidRating($rating);
        }
        $this->saveRating($talkId, $rating);
    }
}
