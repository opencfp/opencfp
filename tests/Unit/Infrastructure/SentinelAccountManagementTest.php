<?php

namespace OpenCFP\Test\Unit\Infrastructure;

use Cartalyst\Sentinel\Activations;
use Cartalyst\Sentinel\Roles;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users;
use Illuminate\Support\Collection;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentinelAccountManagement
 */
final class SentinelAccountManagementTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(SentinelAccountManagement::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testImplementsAccountManagementInterface()
    {
        $reflection = new \ReflectionClass(SentinelAccountManagement::class);

        $this->assertTrue($reflection->implementsInterface(AccountManagement::class));
    }

    public function testFindByIdThrowsUserNotFoundExceptionIfUserNotFound()
    {
        $id = $this->getFaker()->numberBetween(1);

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->identicalTo($id))
            ->willReturn(null);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a user matching %s',
            $id
        ));

        $accountManagement->findById($id);
    }

    public function testFindByIdReturnsUserIfUserFound()
    {
        $id = $this->getFaker()->numberBetween(1);

        $sentinelUser = $this->createSentinelUserMock();

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->identicalTo($id))
            ->willReturn($sentinelUser);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $user = $accountManagement->findById($id);

        $this->assertInstanceOf(SentinelUser::class, $user);
        $this->assertSame($sentinelUser, $user->getUser());
    }

    public function testFindByLoginThrowsUserNotFoundExceptionIfUserNotFound()
    {
        $email = $this->getFaker()->email;

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn(null);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a user matching %s',
            $email
        ));

        $accountManagement->findByLogin($email);
    }

    public function testFindByLoginReturnsUserIfUserFound()
    {
        $email = $this->getFaker()->email;

        $sentinelUser = $this->createSentinelUserMock();

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn($sentinelUser);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $user = $accountManagement->findByLogin($email);

        $this->assertInstanceOf(SentinelUser::class, $user);
        $this->assertSame($sentinelUser, $user->getUser());
    }

    public function testFindByRoleReturnsArrayOfUsers()
    {
        $role = $this->getFaker()->word;

        $sentinelUsers = \array_map(function () {
            return $this->createSentinelUserMock();
        }, \array_fill(0, 5, null));

        $sentinelRole = $this->createSentinelRoleMock();

        $sentinelUserCollection = $this->createCollectionMock();
        
        $sentinelUserCollection
            ->expects($this->once())
            ->method('toArray')
            ->willReturn($sentinelUsers);
        
        $sentinelRole
            ->expects($this->once())
            ->method('getUsers')
            ->willReturn($sentinelUserCollection);

        $sentinelRoleRepository = $this->createSentinelRoleRepositoryMock();

        $sentinelRoleRepository
            ->expects($this->once())
            ->method('findByName')
            ->with($this->identicalTo($role))
            ->willReturn($sentinelRole);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getRoleRepository')
            ->willReturn($sentinelRoleRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->assertSame($sentinelUsers, $accountManagement->findByRole($role));
    }

    public function testCreateThrowsUserExistsExceptionIfUserExistsWithEmail()
    {
        $faker = $this->getFaker();

        $email    = $faker->email;
        $password = $faker->password;

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn($this->createSentinelUserMock());

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(UserExistsException::class);

        $accountManagement->create(
            $email,
            $password
        );
    }

    public function testCreateThrowsUserExistsExceptionIfCreationOfUserFailed()
    {
        $faker = $this->getFaker();

        $email    = $faker->email;
        $password = $faker->password;
        $data     = \array_combine(
            $faker->words,
            $faker->words
        );

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->at(0))
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn(null);

        $sentinelUserRepository
            ->expects($this->at(1))
            ->method('create')
            ->with($this->identicalTo(\array_merge(
                [
                    'email'    => $email,
                    'password' => $password,
                ],
                $data
            )))
            ->willReturn(null);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->exactly(2))
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(UserExistsException::class);

        $accountManagement->create(
            $email,
            $password,
            $data
        );
    }

    public function testCreateReturnsUserCreationOfUserSucceeded()
    {
        $faker = $this->getFaker();

        $email    = $faker->email;
        $password = $faker->password;
        $data     = \array_combine(
            $faker->words,
            $faker->words
        );

        $sentinelUser = $this->createSentinelUserMock();

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->at(0))
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn(null);

        $sentinelUserRepository
            ->expects($this->at(1))
            ->method('create')
            ->with($this->identicalTo(\array_merge(
                [
                    'email'    => $email,
                    'password' => $password,
                ],
                $data
            )))
            ->willReturn($sentinelUser);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->exactly(2))
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $user = $accountManagement->create(
            $email,
            $password,
            $data
        );

        $this->assertInstanceOf(SentinelUser::class, $user);
        $this->assertSame($sentinelUser, $user->getUser());
    }

    public function testActivatePassesThroughExceptionIfUserNotFound()
    {
        $email = $this->getFaker()->email;

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn(null);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a user matching %s',
            $email
        ));

        $accountManagement->activate($email);
    }

    public function testActivateCompletesActivationIfUserFound()
    {
        $faker = $this->getFaker();

        $email          = $faker->email;
        $activationCode = $faker->sha256;
        $isActivated    = $faker->boolean();

        $sentinelUser = $this->createSentinelUserMock();

        $sentinelUserRepository = $this->createSentinelUserRepositoryMock();

        $sentinelUserRepository
            ->expects($this->once())
            ->method('findByCredentials')
            ->with($this->identicalTo([
                'email' => $email,
            ]))
            ->willReturn($sentinelUser);

        $sentinelActivation = $this->createSentinelActivationMock();

        $sentinelActivation
            ->expects($this->once())
            ->method('getCode')
            ->willReturn($activationCode);

        $sentinelActivationRepository = $this->createSentinelActivationRepositoryMock();

        $sentinelActivationRepository
            ->expects($this->at(0))
            ->method('create')
            ->with($this->identicalTo($sentinelUser))
            ->willReturn($sentinelActivation);

        $sentinelActivationRepository
            ->expects($this->at(1))
            ->method('complete')
            ->with(
                $this->identicalTo($sentinelUser),
                $this->identicalTo($activationCode)
            )
            ->willReturn($isActivated);

        $sentinel = $this->createSentinelMock();

        $sentinel
            ->expects($this->once())
            ->method('getUserRepository')
            ->willReturn($sentinelUserRepository);

        $sentinel
            ->expects($this->exactly(2))
            ->method('getActivationRepository')
            ->willReturn($sentinelActivationRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->assertSame($isActivated, $accountManagement->activate($email));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Sentinel
     */
    private function createSentinelMock(): Sentinel
    {
        return $this->createMock(Sentinel::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Users\UserRepositoryInterface
     */
    private function createSentinelUserRepositoryMock(): Users\UserRepositoryInterface
    {
        return $this->createMock(Users\UserRepositoryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Users\UserInterface
     */
    private function createSentinelUserMock(): Users\UserInterface
    {
        return $this->createMock(Users\UserInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Roles\RoleRepositoryInterface
     */
    private function createSentinelRoleRepositoryMock(): Roles\RoleRepositoryInterface
    {
        return $this->createMock(Roles\RoleRepositoryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Roles\RoleInterface
     */
    private function createSentinelRoleMock(): Roles\RoleInterface
    {
        return $this->createMock(Roles\RoleInterface::class);
    }

    /**
     * @return Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createCollectionMock(): Collection
    {
        return $this->createMock(Collection::class);
    }

    /**
     * @return Activations\ActivationRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSentinelActivationRepositoryMock(): Activations\ActivationRepositoryInterface
    {
        return $this->createMock(Activations\ActivationRepositoryInterface::class);
    }

    /**
     * @return Activations\ActivationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSentinelActivationMock(): Activations\ActivationInterface
    {
        return $this->createMock(Activations\ActivationInterface::class);
    }
}
