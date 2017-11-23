<?php

namespace OpenCFP\Domain\Services\TalkRating;

final class OneToTenRating extends TalkRating
{
    public function isValidRating(int $rating): bool
    {
        return $rating >= 0 && $rating <= 10;
    }

    public function getRatingName(): string
    {
        return 'onetoten';
    }
}
