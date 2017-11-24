<?php

namespace OpenCFP\Domain\Services\TalkRating;

class YesNoRating extends TalkRating
{
    public function isValidRating(int $rating): bool
    {
        return ($rating >= -1 && $rating <= 1);
    }
}
