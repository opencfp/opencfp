<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;

interface TalkRepository
{
    /**
     * @param Talk $talk
     *
     * @return mixed
     */
    public function persist(Talk $talk);
}
