<?php

namespace OpenCFP\Test\Console;

use Mockery;
use OpenCFP\Console\Application;
use OpenCFP\Console\Command;
use Symfony\Component\Console;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testIsConsoleApplication()
    {
        $application = new Application($this->getApplicationMock());

        $this->assertInstanceOf(Console\Application::class, $application);
    }

    public function testConstructorSetsName()
    {
        $application = new Application($this->getApplicationMock());

        $this->assertSame('OpenCFP', $application->getName());
    }

    public function testConstructorAddsInputOptionForEnvironment()
    {
        $application = new Application($this->getApplicationMock());

        $inputDefinition = $application->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('env'));

        $option = $inputDefinition->getOption('env');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('The environment the command should run in', $option->getDescription());
    }

    public function testConstructorSetsApplication()
    {
        $actualApplication = $this->getApplicationMock();

        $application = new Application($actualApplication);

        $this->assertSame($actualApplication, $application->getContainer());
    }

    public function testHasDefaultCommands()
    {
        $application = new Application($this->getApplicationMock());

        $expected = [
            Console\Command\HelpCommand::class,
            Console\Command\ListCommand::class,
            Command\AdminDemoteCommand::class,
            Command\AdminPromoteCommand::class,
            Command\ClearCacheCommand::class,
        ];

        $actual = array_map(function (Console\Command\Command $command) {
            return get_class($command);
        }, $application->getDefaultCommands());

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return Mockery\MockInterface|\OpenCFP\Application
     */
    private function getApplicationMock()
    {
        return Mockery::mock(\OpenCFP\Application::class);
    }
}
