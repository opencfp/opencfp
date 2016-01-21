<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use Mockery as m;
use OpenCFP\Domain\Entity;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use Spot\Mapper;

class SpotTalkRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsTalkRepository()
    {
        $mapper = $this->getMapperMock();

        $repository = new SpotTalkRepository($mapper);

        $this->assertInstanceOf(TalkRepository::class, $repository);
    }

    public function testPersistSavesTalk()
    {
        $talk = $this->getTalkMock();

        $mapper = $this->getMapperMock();

        $mapper
            ->shouldReceive('save')
            ->once()
            ->with($talk)
        ;

        $repository = new SpotTalkRepository($mapper);

        $repository->persist($talk);
    }

    //
    // Factory Methods
    //

    /**
     * @return m\MockInterface|Mapper
     */
    private function getMapperMock()
    {
        return m::mock(Mapper::class);
    }

    /**
     * @return m\MockInterface|Entity\Talk
     */
    private function getTalkMock()
    {
        return m::mock(Entity\Talk::class);
    }
}
