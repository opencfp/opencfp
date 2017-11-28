<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Persistence;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Speaker;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository
 */
final class IlluminateSpeakerRepositoryTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testImplementsSpeakerRepository()
    {
        $reflection = new \ReflectionClass(IlluminateSpeakerRepository::class);

        $this->assertTrue($reflection->implementsInterface(Speaker\SpeakerRepository::class));
    }

    public function testFindByIdThrowsEntityNotFoundExceptionIfUserNotFound()
    {
        $id = $this->getFaker()->numberBetween(1);

        $userModel = $this->createUserMock([
            'findOrFail',
        ]);

        $userModel
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->identicalTo($id))
            ->willThrowException(new Eloquent\ModelNotFoundException());

        $repository = new IlluminateSpeakerRepository($userModel);

        $this->expectException(EntityNotFoundException::class);

        $repository->findById($id);
    }

    public function testFindByIdReturnsUserIfUserFound()
    {
        $id = $this->getFaker()->numberBetween(1);

        $user = $this->createUserMock();

        $userModel = $this->createUserMock([
            'findOrFail',
        ]);

        $userModel
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->identicalTo($id))
            ->willReturn($user);

        $repository = new IlluminateSpeakerRepository($userModel);

        $this->assertSame($user, $repository->findById($id));
    }

    public function testPersistSavesUser()
    {
        $user = $this->createUserMock([
            'save',
        ]);

        $user
            ->expects($this->once())
            ->method('save');

        $repository = new IlluminateSpeakerRepository($this->createUserMock());

        $repository->persist($user);
    }

    /**
     * @param string[] $methods
     *
     * @return Model\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserMock(array $methods = []): Model\User
    {
        return $this->createPartialMock(
            Model\User::class,
            $methods
        );
    }
}
