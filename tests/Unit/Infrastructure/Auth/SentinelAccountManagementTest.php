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

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentinel\Roles;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users;
use Illuminate\Support\Collection;
use Localheinz\Test\Util\Helper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\RoleNotFoundException;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;

final class SentinelAccountManagementTest extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(SentinelAccountManagement::class);
    }

    /**
     * @test
     */
    public function instanceOfAccountManagement()
    {
        $this->assertClassImplementsInterface(AccountManagement::class, SentinelAccountManagement::class);
    }

    /**
     * @test
     */
    public function findByIdThrowsCorrectError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findById')->andReturn(null);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserNotFoundException::class);
        $account->findById(3);
    }

    /**
     * @test
     */
    public function findByIdReturnsSentinelUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findById')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->findById(3));
    }

    /**
     * @test
     */
    public function findByLoginThrowsCorrectError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserNotFoundException::class);
        $account->findByLogin('mail@mail.mail');
    }

    /**
     * @test
     */
    public function findByLoginReturnsSentinelUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class)->makePartial();
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->findByLogin('mail@mail.mail'));
    }

    /**
     * @test
     */
    public function findByRoleThrowsRoleNotFoundExceptionIfRoleWasNotFound()
    {
        $name = $this->faker()->word;

        $roleRepository = Mockery::mock(Roles\IlluminateRoleRepository::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($name)
            ->andReturn(null);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a role with name "%s".',
            $name
        ));

        $accountManagement->findByRole($name);
    }

    /**
     * @test
     */
    public function findByRoleReturnsArrayOfUsers()
    {
        $name = $this->faker()->word;

        $users = [
            Mockery::mock(Users\UserInterface::class),
            Mockery::mock(Users\UserInterface::class),
            Mockery::mock(Users\UserInterface::class),
        ];

        $userCollection = Mockery::mock(Collection::class);

        $userCollection
            ->shouldReceive('toArray')
            ->andReturn($users);

        $role = Mockery::mock(Roles\EloquentRole::class);

        $role
            ->shouldReceive('getUsers')
            ->andReturn($userCollection);

        $roleRepository = Mockery::mock(Roles\IlluminateRoleRepository::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($name)
            ->andReturn($role);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $accounts = new SentinelAccountManagement($sentinel);

        $this->assertSame($users, $accounts->findByRole($name));
    }

    /**
     * @test
     */
    public function createThrowsCorrectErrorWhenUserAlreadyExists()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserExistsException::class);
        $account->create('mail@mail.mail', 'pass');
    }

    /**
     * @test
     */
    public function createReturnsCorrectUserWhenCreatingOne()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $sentinel->shouldReceive('getUserRepository->create')->andReturn($user);
        $account = new SentinelAccountManagement($sentinel);
        $this->assertInstanceOf(SentinelUser::class, $account->create('mail@mail.mail', 'pass'));
    }

    /**
     * @test
     */
    public function createDefaultsToThrowingError()
    {
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn(null);
        $sentinel->shouldReceive('getUserRepository->create')->andReturn(false);
        $account = new SentinelAccountManagement($sentinel);
        $this->expectException(UserExistsException::class);
        $account->create('mail@mail.mail', 'pass');
    }

    /**
     * @test
     */
    public function activateActivatesUser()
    {
        $user     = Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $sentinel = Mockery::mock(Sentinel::class);
        $sentinel->shouldReceive('getUserRepository->findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('getActivationRepository->create->getCode');
        $sentinel->shouldReceive('getActivationRepository->complete')->andReturn(true);
        $account = new SentinelAccountManagement($sentinel);

        $account->activate('mail@mail');
    }

    /**
     * @test
     */
    public function promoteToThrowsRoleNotFoundExceptionIfRoleWasNotFound()
    {
        $faker = $this->faker();

        $email = $faker->word;
        $name  = $faker->word;

        $roleRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($name)
            ->andReturn(null);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a role with name "%s".',
            $name
        ));

        $accountManagement->promoteTo(
            $email,
            $name
        );
    }

    /**
     * @test
     */
    public function promoteToAttachesUserToUserCollection()
    {
        $faker = $this->faker();

        $userId   = $faker->numberBetween(1);
        $email    = $faker->word;
        $roleName = $faker->word;

        $user = Mockery::mock(Users\UserInterface::class);

        $user
            ->shouldReceive('getUserId')
            ->andReturn($userId);

        $userCollection = Mockery::mock(Collection::class);

        $userCollection
            ->shouldReceive('attach')
            ->with($userId);

        $role = Mockery::mock(Roles\EloquentRole::class);

        $role
            ->shouldReceive('users')
            ->andReturn($userCollection);

        $roleRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($roleName)
            ->andReturn($role);

        $userRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $userRepository
            ->shouldReceive('findByCredentials')
            ->with([
                'email' => $email,
            ])
            ->andReturn($user);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $sentinel
            ->shouldReceive('getUserRepository')
            ->andReturn($userRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $accountManagement->promoteTo(
            $email,
            $roleName
        );
    }

    /**
     * @test
     */
    public function demoteFromThrowsRoleNotFoundExceptionIfRoleWasNotFound()
    {
        $faker = $this->faker();

        $email = $faker->word;
        $name  = $faker->word;

        $roleRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($name)
            ->andReturn(null);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to find a role with name "%s".',
            $name
        ));

        $accountManagement->demoteFrom(
            $email,
            $name
        );
    }

    /**
     * @test
     */
    public function demoteFromDetachesUserFromUserCollection()
    {
        $faker = $this->faker();

        $userId   = $faker->numberBetween(1);
        $email    = $faker->word;
        $roleName = $faker->word;

        $user = Mockery::mock(Users\UserInterface::class);

        $user
            ->shouldReceive('getUserId')
            ->andReturn($userId);

        $userCollection = Mockery::mock(Collection::class);

        $userCollection
            ->shouldReceive('detach')
            ->with($userId);

        $role = Mockery::mock(Roles\EloquentRole::class);

        $role
            ->shouldReceive('users')
            ->andReturn($userCollection);

        $roleRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $roleRepository
            ->shouldReceive('findByName')
            ->with($roleName)
            ->andReturn($role);

        $userRepository = Mockery::mock(Roles\RoleRepositoryInterface::class);

        $userRepository
            ->shouldReceive('findByCredentials')
            ->with([
                'email' => $email,
            ])
            ->andReturn($user);

        $sentinel = Mockery::mock(Sentinel::class);

        $sentinel
            ->shouldReceive('getRoleRepository')
            ->andReturn($roleRepository);

        $sentinel
            ->shouldReceive('getUserRepository')
            ->andReturn($userRepository);

        $accountManagement = new SentinelAccountManagement($sentinel);

        $accountManagement->demoteFrom(
            $email,
            $roleName
        );
    }
}
