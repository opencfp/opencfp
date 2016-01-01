<?php

namespace OpenCFP\Infrastructure\Persistence;

use Faker\Factory;
use Faker\Generator;
use Mockery as m;
use OpenCFP\Domain\Entity;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use Spot\Mapper;

class SpotSpeakerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsSpeakerRepository()
    {
        $mapper = $this->getMapperMock();

        $repository = new SpotSpeakerRepository($mapper);

        $this->assertInstanceOf(SpeakerRepository::class, $repository);
    }

    public function testFindByThrowsEntityNotFoundException()
    {
        $this->setExpectedException(EntityNotFoundException::class, '');

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

    public function testPersistDoesNothing()
    {
        $mapper = $this->getMapperMock();

        $mapper->shouldNotReceive(m::any());

        $speaker = $this->getUserMock();

        $speaker->shouldNotReceive(m::any());

        $repository = new SpotSpeakerRepository($mapper);

        $repository->persist($speaker);
    }

    /**
     * @return Generator
     */
    private function getFaker()
    {
        static $faker;

        if ($faker === null) {
            $faker = Factory::create();
        }

        return $faker;
    }

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
