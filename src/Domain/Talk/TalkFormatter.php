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

namespace OpenCFP\Domain\Talk;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Services\TalkFormat;

class TalkFormatter implements TalkFormat
{
    /**
     * Iterates over a collection of DBAL objects and returns a formatted result set
     *
     * @param Collection $talkCollection Collection of Talks
     * @param int        $adminUserId
     *
     * @return Collection
     */
    public function formatList(Collection $talkCollection, int $adminUserId): Collection
    {
        return $talkCollection->map(function ($talk) use ($adminUserId) {
            return new TalkProfile($talk, $adminUserId);
        });
    }
}
