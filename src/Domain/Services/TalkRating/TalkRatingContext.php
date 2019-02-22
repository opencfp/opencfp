<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services\TalkRating;

use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;

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
