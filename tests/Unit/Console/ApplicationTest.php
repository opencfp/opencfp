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

namespace OpenCFP\Test\Unit\Console;

use Localheinz\Test\Util\Helper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Console\Application;
use Symfony\Component\Console;

/**
 * @group db
 * @covers \OpenCFP\Console\Application
 */
final class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;

    public function testIsConsoleApplication()
    {
        $this->assertClassExtends(Console\Application::class, Application::class);
    }

    public function testConstructorSetsName()
    {
        $application = new Application();

        $this->assertSame('OpenCFP', $application->getName());
    }

    public function testConstructorAddsInputOptionForEnvironment()
    {
        $application = new Application();

        $inputDefinition = $application->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('env'));

        $option = $inputDefinition->getOption('env');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('The environment the command should run in', $option->getDescription());
    }

    public function testHasDefaultCommands()
    {
        $application = new Application();

        $expected = [
            Console\Command\HelpCommand::class,
            Console\Command\ListCommand::class,
        ];

        $actual = \array_map(function (Console\Command\Command $command) {
            return \get_class($command);
        }, $application->getDefaultCommands());

        \sort($expected);
        \sort($actual);

        $this->assertSame($expected, $actual);
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
