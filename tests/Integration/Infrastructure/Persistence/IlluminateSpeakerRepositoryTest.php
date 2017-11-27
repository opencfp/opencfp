<?php

namespace OpenCFP\Test\Integration\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @coversNothing
 */
class IlluminateSpeakerRepositoryTest extends BaseTestCase
{
    use GeneratorTrait;
    use RefreshDatabase;

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
            ->andReturn($speaker);

        $repository = new IlluminateSpeakerRepository($speaker);

        $this->assertSame($speaker, $repository->findById($id));
    }

    /**
     * @test
     */
    public function persistSavesToDatabase()
    {
        $repo = new IlluminateSpeakerRepository(new User());
        //Check There are no users in the database
        $this->assertSame(0, User::count());

        $user = new User([
            'email'    => 'texst@example.com',
            'password' => 'NotHashedNow',
        ]);

        //User hasn't been saved yet.
        $this->assertSame(0, User::count());

        $repo->persist($user);

        $this->assertSame(1, User::count());
    }
}
