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

namespace OpenCFP\Test\Unit\Console\Command;

use Localheinz\Test\Util\Helper;
use OpenCFP\Console\Command\UserCreateCommand;
use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Symfony\Component\Console;

final class UserCreateCommandTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(UserCreateCommand::class);
    }

    /**
     * @test
     */
    public function extendsCommand()
    {
        $this->assertClassExtends(Console\Command\Command::class, UserCreateCommand::class);
    }

    /**
     * @test
     */
    public function hasNameAndDescription()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $this->assertSame('user:create', $command->getName());
        $this->assertSame('Creates a new user', $command->getDescription());
    }

    /**
     * @test
     */
    public function hasFirstNameOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('first_name'));

        $option = $inputDefinition->getOption('first_name');

        $this->assertSame('f', $option->getShortcut());
        $this->assertSame('First Name of the user to create', $option->getDescription());
        $this->assertTrue($option->isValueRequired());
        $this->assertNull($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function hasLastNameOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('last_name'));

        $option = $inputDefinition->getOption('last_name');

        $this->assertSame('l', $option->getShortcut());
        $this->assertSame('Last Name of the user to create', $option->getDescription());
        $this->assertTrue($option->isValueRequired());
        $this->assertNull($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function hasEmailOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('email'));

        $option = $inputDefinition->getOption('email');

        $this->assertSame('m', $option->getShortcut());
        $this->assertSame('Email of the user to create', $option->getDescription());
        $this->assertTrue($option->isValueRequired());
        $this->assertNull($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function hasPasswordOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('password'));

        $option = $inputDefinition->getOption('password');

        $this->assertSame('p', $option->getShortcut());
        $this->assertSame('Password of the user to create', $option->getDescription());
        $this->assertTrue($option->isValueRequired());
        $this->assertNull($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function hasAdminOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('admin'));

        $option = $inputDefinition->getOption('admin');

        $this->assertSame('a', $option->getShortcut());
        $this->assertSame('Promote to administrator', $option->getDescription());
        $this->assertFalse($option->isValueRequired());
        $this->assertFalse($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function hasReviewerOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('reviewer'));

        $option = $inputDefinition->getOption('reviewer');

        $this->assertSame('r', $option->getShortcut());
        $this->assertSame('Promote to reviewer', $option->getDescription());
        $this->assertFalse($option->isValueRequired());
        $this->assertFalse($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    /**
     * @test
     */
    public function executeFailsIfUserExists()
    {
        $faker = $this->faker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $password  = $faker->password;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($password),
                $this->identicalTo([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'password'   => $password,
                ])
            )
            ->willThrowException(new Auth\UserExistsException());

        $command = new UserCreateCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            '--first_name' => $firstName,
            '--last_name'  => $lastName,
            '--email'      => $email,
            '--password'   => $password,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $message = \sprintf(
            'A user with the login "%s" already exists.',
            $email
        );

        $this->assertContains($message, $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeSucceedsIfUserDoesNotExist()
    {
        $faker = $this->faker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $password  = $faker->password;

        $user = $this->createUserMock();

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->at(0))
            ->method('create')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($password),
                $this->identicalTo([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'password'   => $password,
                ])
            )
            ->willReturn($user);

        $accountManagement
            ->expects($this->at(1))
            ->method('activate')
            ->with($this->identicalTo($email));

        $command = new UserCreateCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            '--first_name' => $firstName,
            '--last_name'  => $lastName,
            '--email'      => $email,
            '--password'   => $password,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertContains('Creating User', $commandTester->getDisplay());

        $creationMessage = \sprintf(
            ' * created user with login %s',
            $email
        );

        $this->assertContains($creationMessage, $commandTester->getDisplay());
        $this->assertContains('User Created', $commandTester->getDisplay());
    }

    /**
     * @dataProvider providerOptionsAndRoles
     *
     * @param string[] $options
     * @param string[] $roles
     *
     * @test
     */
    public function executeSucceedsIfUserDoesNotExistAndPromotesUser(array $options, array $roles)
    {
        $faker = $this->faker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $password  = $faker->password;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->at(0))
            ->method('create')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($password),
                $this->identicalTo([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'password'   => $password,
                ])
            )
            ->willReturn($this->createUserMock());

        $accountManagement
            ->expects($this->at(1))
            ->method('activate')
            ->with($this->identicalTo($email));

        $command = new UserCreateCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $options = \array_merge([
            '--first_name' => $firstName,
            '--last_name'  => $lastName,
            '--email'      => $email,
            '--password'   => $password,
        ], $options);

        $commandTester->execute($options);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertContains('Creating User', $commandTester->getDisplay());

        $creationMessage = \sprintf(
            ' * created user with login %s',
            $email
        );

        $this->assertContains($creationMessage, $commandTester->getDisplay());

        $promotionMessage = \implode(PHP_EOL, \array_map(function (string $role) {
            return \sprintf(
                ' * promoted user to %s',
                $role
            );
        }, $roles));

        $this->assertContains($promotionMessage, $commandTester->getDisplay());
        $this->assertContains('User Created', $commandTester->getDisplay());
    }

    public function providerOptionsAndRoles(): \Generator
    {
        $values = [
            'admin-only' => [
                [
                    '--admin' => null,
                ],
                [
                    'admin',
                ],
            ],
            'reviewer-only' => [
                [
                    '--reviewer' => null,
                ],
                [
                    'reviewer',
                ],
            ],
            'admin-and-reviewer-only' => [
                [
                    '--admin'    => null,
                    '--reviewer' => null,
                ],
                [
                    'admin',
                    'reviewer',
                ],
            ],
        ];

        foreach ($values as $key => list($options, $roles)) {
            yield $key => [
                $options,
                $roles,
            ];
        }
    }

    /**
     * @deprecated
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Services\AccountManagement
     */
    private function createAccountManagementMock(): Services\AccountManagement
    {
        return $this->createMock(Services\AccountManagement::class);
    }

    /**
     * @deprecated
     *
     * @return Auth\UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserMock(): Auth\UserInterface
    {
        return $this->createMock(Auth\UserInterface::class);
    }
}
