<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use Mockery as m;
use OpenCFP\Domain\Entity;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use Spot\Mapper;

class SpotTalkRepositoryTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsTalkRepository()
    {
        $mapper = $this->getMapperMock();

        $repository = new SpotTalkRepository($mapper);

        $this->assertInstanceOf(TalkRepository::class, $repository);
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
}
