<?php

namespace OpenCFP\Domain\Services\TalkRating;

class TalkRatingException extends \RuntimeException
{
    public static function invalidRating($rating): self
    {
        return new self(sprintf('Invalid talk rating: %s', $rating));
    }
}
