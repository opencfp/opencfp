<?php

namespace OpenCFP\Domain\Talk;

interface TalkRepository
{
    /**
     * @param $talk
     *
     * @return mixed
     */
    public function persist($talk);
}
