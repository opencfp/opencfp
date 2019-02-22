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

class YesNoRating extends TalkRating
{
    public function isValidRating(int $rating): bool
    {
        return $rating >= -1 && $rating <= 1;
    }
}
