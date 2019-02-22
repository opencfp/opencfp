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
use OpenCFP\Console\Command\UserDemoteCommand;
use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use PHPUnit\Framework;
use Symfony\Component\Console;

final class UserDemoteCommandTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isFinal()
    {
        $this->assertClassIsFinal(UserDemoteCommand::class);
    }

    /**
     * @test
     */
    public function extendsCommand()
    {
        $this->assertClassExtends(Console\Command\Command::class, UserDemoteCommand::class);
    }

    /**
     * @test
     */
    public function hasNameAndDescription()
    {
        $command = new UserDemoteCommand($this->createAccountManagementMock());

        $this->assertSame('user:demote', $command->getName());
        $this->assertSame('Demote an existing user from a role', $command->getDescription());
    }

    /**
     * @test
     */
    public function hasEmailArgument()
    {
        $command = new UserDemoteCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasArgument('email'));

        $argument = $inputDefinition->getArgument('email');

        $this->assertSame('Email address of user', $argument->getDescription());
        $this->assertTrue($argument->isRequired());
        $this->assertNull($argument->getDefault());
        $this->assertFalse($argument->isArray());
    }

    /**
     * @test
     */
    public function hasRoleNameArgument()
    {
        $command = new UserDemoteCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasArgument('role-name'));

        $argument = $inputDefinition->getArgument('role-name');

        $this->assertSame('Name of role user should be demoted from', $argument->getDescription());
        $this->assertTrue($argument->isRequired());
        $this->assertNull($argument->getDefault());
        $this->assertFalse($argument->isArray());
    }

    /**
     * @test
     */
    public function executeFailsIfUserWasNotFound()
    {
        $faker = $this->faker();

        $email    = $faker->email;
        $roleName = $faker->word;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('demoteFrom')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($roleName)
            )
            ->willThrowException(new Auth\UserNotFoundException());

        $command = new UserDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email'     => $email,
            'role-name' => $roleName,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "%s"',
            $email,
            $roleName
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $failureMessage = \sprintf(
            'Could not find account with email "%s".',
            $email
        );

        $this->assertContains($failureMessage, $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeFailsIfRoleWasNotFound()
    {
        $faker = $this->faker();

        $email    = $faker->email;
        $roleName = $faker->word;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('demoteFrom')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($roleName)
            )
            ->willThrowException(new Auth\RoleNotFoundException());

        $command = new UserDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email'     => $email,
            'role-name' => $roleName,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "%s"',
            $email,
            $roleName
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $failureMessage = \sprintf(
            'Could not find role with name "%s".',
            $roleName
        );

        $this->assertContains($failureMessage, $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeFailsIfDemoteFromThrowsGenericException()
    {
        $faker = $this->faker();

        $email    = $faker->email;
        $roleName = $faker->word;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('demoteFrom')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($roleName)
            )
            ->willThrowException(new \Exception());

        $command = new UserDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email'     => $email,
            'role-name' => $roleName,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "%s"',
            $email,
            $roleName
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $failureMessage = \sprintf(
            'Could not demote account with email "%s".',
            $email
        );

        $this->assertContains($failureMessage, $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeSucceedsIfUserAndRoleWereFound()
    {
        $faker = $this->faker();

        $email    = $faker->email;
        $roleName = $faker->word;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('demoteFrom')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo($roleName)
            );

        $command = new UserDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email'     => $email,
            'role-name' => $roleName,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "%s"',
            $email,
            $roleName
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $successMessage = \sprintf(
            'Removed account with email "%s" from the "%s" group.',
            $email,
            $roleName
        );

        $this->assertContains($successMessage, $commandTester->getDisplay());
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
}
