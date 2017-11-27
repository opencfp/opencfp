<?php

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
