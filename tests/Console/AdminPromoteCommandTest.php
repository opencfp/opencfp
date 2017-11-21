<?php

namespace OpenCFP\Test\Console;

use Mockery;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Environment;
use OpenCFP\Infrastructure\Auth\UserInterface;

/**
 * Class AdminPromoteTest
 *
 * @group db
 */
class AdminPromoteCommandTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function promoteDetectsNonExistentUser()
    {
        // Create our input and output dependencies
        $input  = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andThrow(new \Cartalyst\Sentry\Users\UserNotFoundException);
        $app                           = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app[AccountManagement::class] = $accounts;

        // Create our command object and inject our application
        $command = new \OpenCFP\Console\Command\AdminPromoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    /**
     * @test
     */
    public function promoteExistingNonAdminAccount()
    {
        // Create our input and output dependencies
        $input  = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock User that has admin access and add an `addGroup`
         * method that is stubbed out
         */
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('getLogin')->andReturn('test@opencfp.dev');
        $user->shouldReceive('addGroup');

        /**
         * Create a Sentry object that also returns an ID that represents
         * an admin group provider. Number doesn't matter for this particular
         * test
         */
        $accounts = Mockery::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')
            ->andReturn($user);
        $accounts->shouldReceive('promoteTo')
            ->with('test@opencfp.dev', 'Admin');

        // Create our command object and inject our application
        $app                           = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app[AccountManagement::class] = $accounts;
        $command                       = new \OpenCFP\Console\Command\AdminPromoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);

        /**
         * A response of 0 signifies that the console command ran without an
         * error
         */
        $this->assertEquals($response, 0);
    }

    protected function createInputInterfaceWithEmail($email)
    {
        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->shouldReceive('getArgument')->with('email')->andReturn($email);

        return $input;
    }

    protected function createOutputInterface()
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
