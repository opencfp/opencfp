<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use Mockery as m;
use OpenCFP\Domain\Entity;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use OpenCFP\Test\Util\Faker\GeneratorTrait;
use Spot\Mapper;

class SpotSpeakerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    public function testImplementsSpeakerRepository()
    {
        $mapper = $this->getMapperMock();

        $repository = new SpotSpeakerRepository($mapper);

        $this->assertInstanceOf(SpeakerRepository::class, $repository);
    }

    /**
     */
    public function testFindByThrowsEntityNotFoundException()
    {
        $this->expectException(\OpenCFP\Domain\EntityNotFoundException::class);
        $id = $this->getFaker()->randomNumber();

        $mapper = $this->getMapperMock();

        $mapper
            ->shouldReceive('get')
            ->once()
            ->with($id)
            ->andReturn(false)
        ;

        $repository = new SpotSpeakerRepository($mapper);

        $repository->findById($id);
    }

    public function testFindByReturnsSpeaker()
    {
        $id = $this->getFaker()->randomNumber();

        $speaker = $this->getUserMock();

        $mapper = $this->getMapperMock();

        $mapper
            ->shouldReceive('get')
            ->once()
            ->with($id)
            ->andReturn($speaker)
        ;

        $repository = new SpotSpeakerRepository($mapper);

        $this->assertSame($speaker, $repository->findById($id));
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
     * @return m\MockInterface|Entity\User
     */
    private function getUserMock()
    {
        return m::mock(Entity\User::class);
    }
}
