<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Entity\Talk;
use Symfony\Component\EventDispatcher\Event;

class TalkWasSubmitted extends Event
{
    /** @var Talk */
    private $talk;

    public function __construct(Talk $talk)
    {
        $this->talk = $talk;
    }

    public function getTalk()
    {
        return $this->talk;
    }
}
