<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Infrastructure\Persistence;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkRepository;

class IlluminateTalkRepository implements TalkRepository
{
    public function persist(Talk $talk)
    {
        $talk->save();
    }
}
