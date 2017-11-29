<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

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
