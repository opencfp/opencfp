<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Repository;

use Illuminate\Database\Eloquent;
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Repository\UserRepository;
use OpenCFP\Infrastructure\Repository\IlluminateUserRepository;
use PHPUnit\Framework;

final class IlluminateUserRepositoryTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function implementsUserRepository()
    {
        $this->assertClassImplementsInterface(UserRepository::class, IlluminateUserRepository::class);
    }

    /**
     * @test
     */
    public function findByIdThrowsEntityNotFoundExceptionIfUserNotFound()
    {
        $id = $this->faker()->numberBetween(1);

        $userModel = $this->createUserMock([
            'findOrFail',
        ]);

        $userModel
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->identicalTo($id))
            ->willThrowException(new Eloquent\ModelNotFoundException());

        $repository = new IlluminateUserRepository($userModel);

        $this->expectException(EntityNotFoundException::class);

        $repository->findById($id);
    }

    /**
     * @test
     */
    public function findByIdReturnsUserIfUserFound()
    {
        $id = $this->faker()->numberBetween(1);

        $user = $this->createUserMock();

        $userModel = $this->createUserMock([
            'findOrFail',
        ]);

        $userModel
            ->expects($this->once())
            ->method('findOrFail')
            ->with($this->identicalTo($id))
            ->willReturn($user);

        $repository = new IlluminateUserRepository($userModel);

        $this->assertSame($user, $repository->findById($id));
    }

    /**
     * @test
     */
    public function persistSavesUser()
    {
        $user = $this->createUserMock([
            'save',
        ]);

        $user
            ->expects($this->once())
            ->method('save');

        $repository = new IlluminateUserRepository($this->createUserMock());

        $repository->persist($user);
    }

    /**
     * @deprecated
     *
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
