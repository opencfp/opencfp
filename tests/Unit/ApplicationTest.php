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

namespace OpenCFP\Test\Unit;

use OpenCFP\Application;
use OpenCFP\Environment;

/**
 * @covers \OpenCFP\Application
 * @group db
 */
final class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $sut;

    /**
     * @test
     */
    public function it_should_run_and_have_output()
    {
        $this->sut                 = new Application(__DIR__ . '/../..', Environment::testing());
        $this->sut['session.test'] = true;

        // We start an output buffer because the Application sends its response to
        // the output buffer as a Symfony Response.
        \ob_start();
        $this->sut->run();
        $output = \ob_get_clean();

        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function it_should_resolve_configuration_path_based_on_environment()
    {
        $this->sut = new Application(__DIR__ . '/../..', Environment::testing());

        $this->assertTrue($this->sut['env']->isTesting());
        $this->assertContains('testing.yml', $this->sut['path']->configPath());
    }

    /**
     * @test
     */
    public function itIsNotDevOrProdWhenTesting()
    {
        $app = new Application(__DIR__ . '/../..', Environment::testing());

        $this->assertTrue($app['env']->isTesting());
        $this->assertFalse($app['env']->isDevelopment());
        $this->assertFalse($app['env']->isProduction());
    }
}
