<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Console;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Console\Application;
use OpenCFP\Console\Command;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Environment;
use OpenCFP\Infrastructure\Auth\UserExistsException;
use OpenCFP\Infrastructure\Auth\UserInterface;
use Symfony\Component\Console;

/**
 * @group db
 * @covers \OpenCFP\Console\Application
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIsConsoleApplication()
    {
        $application = new Application(new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing()));

        $this->assertInstanceOf(Console\Application::class, $application);
    }

    public function testConstructorSetsName()
    {
        $application = new Application(new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing()));

        $this->assertSame('OpenCFP', $application->getName());
    }

    public function testConstructorAddsInputOptionForEnvironment()
    {
        $application = new Application(new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing()));

        $inputDefinition = $application->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('env'));

        $option = $inputDefinition->getOption('env');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('The environment the command should run in', $option->getDescription());
    }

    public function testConstructorSetsApplication()
    {
        $baseApp     = new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing());
        $application = new Application($baseApp);

        $this->assertAttributeSame($baseApp, 'app', $application);
    }

    public function testHasDefaultCommands()
    {
        $appContainer = new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing());
        $application  = new Application($appContainer);

        $expected = [
            Console\Command\HelpCommand::class,
            Console\Command\ListCommand::class,
            Command\AdminDemoteCommand::class,
            Command\AdminPromoteCommand::class,
            Command\ReviewerPromoteCommand::class,
            Command\ReviewerDemoteCommand::class,
            Command\ClearCacheCommand::class,
        ];

        $actual = \array_map(function (Console\Command\Command $command) {
            return \get_class($command);
        }, $application->getDefaultCommands());

        \sort($expected);
        \sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function testAdminDemoteDetectsNonExistentUser()
    {
        // Create our input and output dependencies
        $input  = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create an AccountManagement mock that throws our expected exception and then
         * add it to our Application mock
         */
        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andThrow(UserExistsException::class);
        $app                           = new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing());
        $app[AccountManagement::class] = $accounts;

        // Create our command object and inject our application
        $command = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    public function testAdminDemoteWillNotDemoteNonAdminAccounts()
    {
        // Create our input and output dependencies
        $input  = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock Sentry object that returns a user that is in the
         * system but does not have admin access
         */
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andReturn($user);
        $app                           = new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing());
        $app[AccountManagement::class] = $accounts;

        // Create our command object and inject our application
        $command = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    public function testAdminDemoteSuccess()
    {
        // Create our input and output dependencies
        $input  = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock User that has admin access and a removeGroup
         * method that is stubbed out
         */
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('getLogin')->andReturn('test@opencfp.dev');
        $user->shouldReceive('removeGroup');

        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')
            ->andReturn($user);
        $accounts->shouldReceive('demoteFrom')
            ->with('test@opencfp.dev', 'Admin');

        // Create our command object and inject our application
        $app                           = new \OpenCFP\Application(__DIR__ . '/../../..', Environment::testing());
        $app[AccountManagement::class] = $accounts;
        $command                       = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 0);
    }

    protected function createInputInterfaceWithEmail($email): \Symfony\Component\Console\Input\InputInterface
    {
        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->shouldReceive('getArgument')->with('email')->andReturn($email);

        return $input;
    }

    protected function createOutputInterface(): \Symfony\Component\Console\Output\OutputInterface
    {
        /**
         * Create a partial mock that stubs out method calls where we don't
         * care about the output and create a formatter object
         */
        $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
        $output->shouldReceive('getVerbosity');
        $output->shouldReceive('write');
        $output->shouldReceive('writeln');
        $output->shouldReceive('isDecorated');
        $formatter = Mockery::mock(\Symfony\Component\Console\Formatter\OutputFormatterInterface::class);
        $formatter->shouldReceive('setDecorated');
        $formatter->shouldReceive('format');
        $formatter->shouldReceive('isDecorated');
        $output->shouldReceive('getFormatter')->andReturn($formatter);

        return $output;
    }
}
