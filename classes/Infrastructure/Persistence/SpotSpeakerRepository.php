<?php

namespace OpenCFP\Infrastructure\Persistence;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use Spot\Mapper;

class SpotSpeakerRepository implements SpeakerRepository
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($speakerId)
    {
        $speaker = $this->mapper->get($speakerId);

        if ($speaker === false) {
            throw new EntityNotFoundException;
        }

        return $speaker;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($speaker)
    {
    }
}
