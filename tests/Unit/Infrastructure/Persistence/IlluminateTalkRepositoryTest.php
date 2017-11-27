<?php

namespace OpenCFP\Test\Unit\Infrastructure\Persistence;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Talk;
use OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository
 */
final class IlluminateTalkRepositoryTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testImplementsTalkRepository()
    {
        $reflection = new \ReflectionClass(IlluminateTalkRepository::class);

        $this->assertTrue($reflection->implementsInterface(Talk\TalkRepository::class));
    }

    public function testPersistSavesTalk()
    {
        $talk = $this->createTalkMock([
            'save',
        ]);

        $talk
            ->expects($this->once())
            ->method('save');

        $repository = new IlluminateTalkRepository();

        $repository->persist($talk);
    }

    /**
     * @param string[] $methods
     *
     * @return Model\Talk|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTalkMock(array $methods = []): Model\Talk
    {
        return $this->createPartialMock(
            Model\Talk::class,
            $methods
        );
    }
}
