<?php

namespace OpenCFP\Infrastructure\Persistence;

use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use Spot\Mapper;

final class SpotSpeakerRepository implements SpeakerRepository
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
     * {@inheritdoc}
     */
    public function findById($speakerId)
    {
        $speaker = $this->mapper->get($speakerId);

        if (false === $speaker) {
            throw new EntityNotFoundException;
        }

        return $speaker;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(User $speaker)
    {
    }
}
