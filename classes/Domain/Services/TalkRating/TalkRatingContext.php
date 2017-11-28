<?php

namespace OpenCFP\Domain\Services\TalkRating;

use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;

class TalkRatingContext
{
    public static function getTalkStrategy(string $strategy, Authentication $auth): TalkRatingStrategy
    {
        $strategy = \strtolower($strategy);
        switch ($strategy) {
            case 'yesno':
                return new YesNoRating(new TalkMeta(), $auth);
            default:
                return new YesNoRating(new TalkMeta(), $auth);
        }
    }
}
