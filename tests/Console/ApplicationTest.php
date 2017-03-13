<?php

namespace OpenCFP\Test\Console;

use Mockery;
use OpenCFP\Console\Application;
use OpenCFP\Console\Command;
use OpenCFP\Environment;
use Symfony\Component\Console;

class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testIsConsoleApplication()
    {
        $application = new Application(new \OpenCFP\Application(BASE_PATH, Environment::testing()));

        $this->assertInstanceOf(Console\Application::class, $application);
    }

    public function testConstructorSetsName()
    {
        $application = new Application(new \OpenCFP\Application(BASE_PATH, Environment::testing()));

        $this->assertSame('OpenCFP', $application->getName());
    }

    public function testConstructorAddsInputOptionForEnvironment()
    {
        $application = new Application(new \OpenCFP\Application(BASE_PATH, Environment::testing()));

        $inputDefinition = $application->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('env'));

        $option = $inputDefinition->getOption('env');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('The environment the command should run in', $option->getDescription());
    }

    public function testConstructorSetsApplication()
    {
        $baseApp = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $application = new Application($baseApp);

        $this->assertAttributeSame($baseApp, 'app', $application);
    }

    public function testHasDefaultCommands()
    {
        $appContainer = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $application = new Application($appContainer);

        $expected = [
            Console\Command\HelpCommand::class,
            Console\Command\ListCommand::class,
            Command\AdminDemoteCommand::class,
            Command\AdminPromoteCommand::class,
            Command\UserCreateCommand::class,
            Command\ClearCacheCommand::class,
            Command\UserAssignRoleCommand::class,
        ];

        $actual = array_map(function (Console\Command\Command $command) {
            return get_class($command);
        }, $application->getDefaultCommands());

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function testGetContainerShouldReturnTheApplicationObject()
    {
        $appContainer = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $application = new Application($appContainer);

        $this->assertEquals(\OpenCFP\Application::class, get_class($application->getContainer()));
    }

    public function testAdminDemoteDetectsNonExistentUser()
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
        $command = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    public function testAdminDemoteWillNotDemoteNonAdminAccounts()
    {
        // Create our input and output dependencies
        $input = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock Sentry object that returns a user that is in the
         * system but does not have admin access
         */
        $user = Mockery::mock('\stdClass');
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $sentry = Mockery::mock('\Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('getUserProvider->findByLogin')
            ->andReturn($user);
        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentry'] = $sentry;

        // Create our command object and inject our application
        $command = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
    }

    public function testAdminDemoteSuccess()
    {
        // Create our input and output dependencies
        $input = $this->createInputInterfaceWithEmail('test@opencfp.dev');
        $output = $this->createOutputInterface();

        /**
         * Create a mock User that has admin access and a removeGroup
         * method that is stubbed out
         */
        $user = Mockery::mock('\stdClass');
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('removeGroup');

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
        $command = new \OpenCFP\Console\Command\AdminDemoteCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 0);
    }

    public function testUserAssignRoleSuccess()
    {
        $input = Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $input->shouldReceive('getOption')->with('email')->andReturn('test@opencfp.org');
        $input->shouldReceive('getOption')->with('role')->andReturn('reviewer');
        $user = new \stdClass();
        $user->roles = [];
        $role = Mockery::mock('\Cartalyst\Sentinel\Roles\RoleInterface');
        $role->shouldReceive('users->attach');
        $sentinel = Mockery::mock('Cartalyst\Sentinel\Native\Facades\Sentinel');
        $sentinel->shouldReceive('findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('findRoleBySlug')->andReturn($role);

        $output = $this->createOutputInterface();

        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentinel'] = $sentinel;
        $command = new Command\UserAssignRoleCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 0);
    }

    public function testUserAssignRoleFailure()
    {
        $input = Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $input->shouldReceive('getOption')->with('email')->andReturn('test@opencfp.org');
        $input->shouldReceive('getOption')->with('role')->andReturn('reviewer');
        $user = new \stdClass();
        $user_role = new \stdClass();
        $user_role->slug = 'reviewer';
        $user->roles = [$user_role];
        $role = Mockery::mock('\Cartalyst\Sentinel\Roles\RoleInterface');
        $role->shouldReceive('users->attach');
        $sentinel = Mockery::mock('Cartalyst\Sentinel\Native\Facades\Sentinel');
        $sentinel->shouldReceive('findByCredentials')->andReturn($user);
        $sentinel->shouldReceive('findRoleBySlug')->andReturn($role);

        $output = $this->createOutputInterface();

        $app = new \OpenCFP\Application(BASE_PATH, Environment::testing());
        $app['sentinel'] = $sentinel;
        $command = new Command\UserAssignRoleCommand();
        $command->setApp($app);
        $response = $command->execute($input, $output);
        $this->assertEquals($response, 1);
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
