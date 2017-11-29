<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Console\Command;

use OpenCFP\Console\Command\ReviewerDemoteCommand;
use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * @covers \OpenCFP\Console\Command\ReviewerDemoteCommand
 */
final class ReviewerDemoteCommandTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(ReviewerDemoteCommand::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testExtendsCommand()
    {
        $command = new ReviewerDemoteCommand($this->createAccountManagementMock());

        $this->assertInstanceOf(Console\Command\Command::class, $command);
    }

    public function testHasNameAndDescription()
    {
        $command = new ReviewerDemoteCommand($this->createAccountManagementMock());

        $this->assertSame('reviewer:demote', $command->getName());
        $this->assertSame('Demote an existing user from reviewer', $command->getDescription());
    }

    public function testHasEmailArgument()
    {
        $command = new ReviewerDemoteCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasArgument('email'));

        $argument = $inputDefinition->getArgument('email');

        $this->assertSame('Email address of user to demote from reviewer', $argument->getDescription());
        $this->assertTrue($argument->isRequired());
        $this->assertNull($argument->getDefault());
        $this->assertFalse($argument->isArray());
    }

    public function testExecuteFailsIfUserDoesNotExist()
    {
        $email= $this->getFaker()->email;

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('findByLogin')
            ->with($this->identicalTo($email))
            ->willThrowException(new Auth\UserNotFoundException());

        $command = new ReviewerDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email' => $email,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "Reviewer"',
            $email
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $failureMessage = \sprintf(
            'Could not find account with email "%s".',
            $email
        );

        $this->assertContains($failureMessage, $commandTester->getDisplay());
    }

    public function testExecuteSucceedsIfUserExists()
    {
        $email = $this->getFaker()->email;

        $user = $this->createUserMock();

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->at(0))
            ->method('findByLogin')
            ->with($this->identicalTo($email))
            ->willReturn($user);

        $accountManagement
            ->expects($this->at(1))
            ->method('demoteFrom')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo('Reviewer')
            );

        $command = new ReviewerDemoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email' => $email,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Demoting account with email "%s" from "Reviewer"',
            $email
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $successMessage = \sprintf(
            'Removed account with email "%s" from the "Reviewer" group',
            $email
        );

        $this->assertContains($successMessage, $commandTester->getDisplay());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Services\AccountManagement
     */
    private function createAccountManagementMock(): Services\AccountManagement
    {
        return $this->createMock(Services\AccountManagement::class);
    }

    /**
     * @return Auth\UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserMock(): Auth\UserInterface
    {
        return $this->createMock(Auth\UserInterface::class);
    }
}
