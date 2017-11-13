<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository;
use OpenCFP\Test\DatabaseTransaction;

class IlluminateTalkRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseTransaction;

    public function setUp()
    {
        $this->setUpDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }

    /**
     * @test
     */
    public function RepoIsInstanceOfTalkRepository()
    {
        $repo = new IlluminateTalkRepository();

        $this->assertInstanceOf(TalkRepository::class, $repo);
    }

    /**
     * @test
     */
    public function persistSavesModelIntoDatabase()
    {
        $talk = factory(Talk::class, 1)->make()->first();
        $repo = new IlluminateTalkRepository();

        $this->assertCount(0, Talk::all());
        $repo->persist($talk);
        $this->assertCount(1, Talk::all());
    }
}
