<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;

interface TalkRepository
{
    public function persist(Talk $talk);
}
