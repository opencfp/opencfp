<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use OpenCFP\Test\Util\Faker\GeneratorTrait;

class IlluminateSpeakerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    /**
     * @test
     */
    public function repoImplementsSpeakerRepo()
    {
        $repo = new IlluminateSpeakerRepository(new User());

        $this->assertInstanceOf(SpeakerRepository::class, $repo);
    }

    /**
     * @test
     */
    public function findByThrowsEntityNotFoundException()
    {
        $id = $this->getFaker()->randomNumber();

        $user = Mockery::mock(User::class);

        $user
            ->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andThrow(ModelNotFoundException::class);

        $repository = new IlluminateSpeakerRepository($user);

        $this->expectException(\OpenCFP\Domain\EntityNotFoundException::class);

        $repository->findById($id);
    }

    /**
     * @test
     */
    public function findByReturnsSpeaker()
    {
        $id = $this->getFaker()->randomNumber();

        $speaker = Mockery::mock(User::class);

        $speaker
            ->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andReturn($speaker)
        ;

        $repository = new IlluminateSpeakerRepository($speaker);

        $this->assertSame($speaker, $repository->findById($id));
    }
}
