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

use OpenCFP\Console\Command\ReviewerPromoteCommand;
use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * @covers \OpenCFP\Console\Command\ReviewerPromoteCommand
 */
final class ReviewerPromoteCommandTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(ReviewerPromoteCommand::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testExtendsCommand()
    {
        $command = new ReviewerPromoteCommand($this->createAccountManagementMock());

        $this->assertInstanceOf(Console\Command\Command::class, $command);
    }

    public function testHasNameAndDescription()
    {
        $command = new ReviewerPromoteCommand($this->createAccountManagementMock());

        $this->assertSame('reviewer:promote', $command->getName());
        $this->assertSame('Promote an existing user to be a reviewer', $command->getDescription());
    }

    public function testHasEmailArgument()
    {
        $command = new ReviewerPromoteCommand($this->createAccountManagementMock());

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasArgument('email'));

        $argument = $inputDefinition->getArgument('email');

        $this->assertSame('Email address of user to promote to reviewer', $argument->getDescription());
        $this->assertTrue($argument->isRequired());
        $this->assertNull($argument->getDefault());
        $this->assertFalse($argument->isArray());
    }

    public function testExecuteFailsIfUserDoesNotExist()
    {
        $email = $this->getFaker()->email;

        $roleName = 'Reviewer';

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->once())
            ->method('findByLogin')
            ->with($this->identicalTo($email))
            ->willThrowException(new Auth\UserNotFoundException());

        $command = new ReviewerPromoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email' => $email,
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Promoting account with email "%s" to "%s"',
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

    public function testExecuteSucceedsIfUserExists()
    {
        $email = $this->getFaker()->email;

        $roleName = 'Reviewer';

        $user = $this->createUserMock();

        $accountManagement = $this->createAccountManagementMock();

        $accountManagement
            ->expects($this->at(0))
            ->method('findByLogin')
            ->with($this->identicalTo($email))
            ->willReturn($user);

        $accountManagement
            ->expects($this->at(1))
            ->method('promoteTo')
            ->with(
                $this->identicalTo($email),
                $this->identicalTo('Reviewer')
            );

        $command = new ReviewerPromoteCommand($accountManagement);

        $commandTester = new Console\Tester\CommandTester($command);

        $commandTester->execute([
            'email' => $email,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $sectionMessage = \sprintf(
            'Promoting account with email "%s" to "%s"',
            $email,
            $roleName
        );

        $this->assertContains($sectionMessage, $commandTester->getDisplay());

        $successMessage = \sprintf(
            'Added account with email "%s" to the "%s" group',
            $email,
            $roleName
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
