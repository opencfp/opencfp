<?php

namespace OpenCFP\Domain\Talk;

interface TalkRepository
{
    /**
     * @param $talk
     */
    public function persist($talk);
}
