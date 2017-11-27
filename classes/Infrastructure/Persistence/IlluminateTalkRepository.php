<?php

namespace OpenCFP\Infrastructure\Persistence;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkRepository;

class IlluminateTalkRepository implements TalkRepository
{
    /**
     * @param Talk $talk
     *
     * @return mixed
     */
    public function persist(Talk $talk)
    {
        return $talk->save();
    }
}
