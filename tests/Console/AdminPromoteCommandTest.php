<?php

namespace OpenCFP\Test\Console;

use Mockery;
use OpenCFP\Environment;

class AdminPromoteTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function promoteDetectsNonExistentUser()
    {
        // Create our input and output dependencies
        $input = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a Sentry mock that throws our expected exception and then
         * add it to our Application mock
         */
        $sentry = Mockery::mock('\Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('getUserProvider->findByLogin')->andThrow(new \Cartalyst\Sentry\Users\UserNotFoundException);
        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentry'] = $sentry;

        // Create our command object and inject our application
        $command = new \OpenCFP\Console\Command\AdminPromoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    /**
     * @test
     */
    public function willNotPromoteExistingAdminAccounts()
    {
        // Create our input and output dependencies
        $input = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock Sentry object that returns a user that is in the
         * system but does not have admin access
         */
        $user = Mockery::mock('\stdClass');
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $sentry = Mockery::mock('\Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('getUserProvider->findByLogin')
            ->andReturn($user);
        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentry'] = $sentry;

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
        $input = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock User that has admin access and a removeGroup
         * method that is stubbed out
         */
        $user = Mockery::mock('\stdClass');
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('addGroup');

        /**
         * Create a Sentry object that also returns an ID that represents
         * an admin group provider. Number doesn't matter for this particular
         * test
         */
        $sentry = Mockery::mock('\Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('getUserProvider->findByLogin')
            ->andReturn($user);
        $sentry->shouldReceive('getGroupProvider->findByName')
            ->with('Admin')
            ->andReturn(1);

        // Create our command object and inject our application
        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentry'] = $sentry;
        $command = new \OpenCFP\Console\Command\AdminPromoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 0);
    }

    protected function createInputInterfaceWithEmail($email)
    {
        $input = Mockery::mock('\Symfony\Component\Console\Input\InputInterface');
        $input->shouldReceive('getArgument')->with('email')->andReturn($email);

        return $input;
    }

    protected function createOutputInterface()
    {
        /**
         * Create a partial mock that stubs out method calls where we don't
         * care about the output and create a formatter object
         */
        $output = Mockery::mock('\Symfony\Component\Console\Output\OutputInterface');
        $output->shouldReceive('getVerbosity');
        $output->shouldReceive('write');
        $output->shouldReceive('writeln');
        $output->shouldReceive('isDecorated');
        $formatter = Mockery::mock('\Symfony\Component\Console\Formatter\OutputFormatterInterface');
        $formatter->shouldReceive('setDecorated');
        $formatter->shouldReceive('format');
        $formatter->shouldReceive('isDecorated');
        $output->shouldReceive('getFormatter')->andReturn($formatter);

        return $output;
    }
}
