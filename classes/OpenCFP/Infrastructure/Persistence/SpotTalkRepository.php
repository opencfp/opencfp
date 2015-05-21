<?php

namespace OpenCFP\Infrastructure\Persistence;

use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Talk\TalkRepository;
use Spot\Mapper;

class SpotTalkRepository implements TalkRepository
{
    /**
     * @var Mapper
     */
    protected $mapper;

    function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param Talk $talk
     *
     * @return mixed
     */
    public function persist(Talk $talk)
    {
        $this->mapper->save($talk);
    }
}