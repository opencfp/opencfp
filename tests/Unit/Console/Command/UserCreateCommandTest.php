<?php

namespace OpenCFP\Test\Unit\Console\Command;

use OpenCFP\Console\Command\UserCreateCommand;
use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * @covers \OpenCFP\Console\Command\UserCreateCommand
 */
final class UserCreateCommandTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(UserCreateCommand::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testExtendsCommand()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $this->assertInstanceOf(Console\Command\Command::class, $command);
    }

    public function testHasNameAndDescription()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $this->assertSame('user:create', $command->getName());
        $this->assertSame('Creates a new user', $command->getDescription());
    }

    public function testHasFirstNameOption()
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

    public function testHasLastNameOption()
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

    public function testHasEmailOption()
    {
        $command = new UserCreateCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('email'));

        $option = $inputDefinition->getOption('email');

        $this->assertSame('e', $option->getShortcut());
        $this->assertSame('Email of the user to create', $option->getDescription());
        $this->assertTrue($option->isValueRequired());
        $this->assertNull($option->getDefault());
        $this->assertFalse($option->isArray());
    }

    public function testHasPasswordOption()
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

    public function testHasAdminOption()
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

    public function testHasReviewerOption()
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

    public function testExecuteFailsIfUserExists()
    {
        $faker = $this->getFaker();

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
            ->willThrowException(new UserExistsException());

        $command = new UserCreateCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            '--first_name' => $firstName,
            '--last_name'  => $lastName,
            '--email'      => $email,
            '--password'   => $password,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $message = sprintf(
            'A user with the login "%s" already exists.',
            $email
        );

        $this->assertContains($message, $commandTester->getDisplay());
    }

    public function testExecuteSucceedsIfUserDoesNotExist()
    {
        $faker = $this->getFaker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $password  = $faker->password;

        $user = $this->createSentryUserMock();

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

        $creationMessage = sprintf(
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
     */
    public function testExecuteSucceedsIfUserDoesNotExistAndPromotesUser(array $options, array $roles)
    {
        $faker = $this->getFaker();

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
            ->willReturn($this->createSentryUserMock());

        $accountManagement
            ->expects($this->at(1))
            ->method('activate')
            ->with($this->identicalTo($email));

        $command = new UserCreateCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $options = array_merge([
            '--first_name' => $firstName,
            '--last_name'  => $lastName,
            '--email'      => $email,
            '--password'   => $password,
        ], $options);

        $commandTester->execute($options);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertContains('Creating User', $commandTester->getDisplay());

        $creationMessage = sprintf(
            ' * created user with login %s',
            $email
        );

        $this->assertContains($creationMessage, $commandTester->getDisplay());

        $promotionMessage = implode(PHP_EOL, array_map(function (string $role) {
            return sprintf(
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
     * @return \PHPUnit_Framework_MockObject_MockObject|Services\AccountManagement
     */
    private function createAccountManagementMock(): Services\AccountManagement
    {
        return $this->createMock(Services\AccountManagement::class);
    }

    /**
     * @return Auth\SentryUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSentryUserMock(): Auth\SentryUser
    {
        return $this->createMock(Auth\SentryUser::class);
    }
}
