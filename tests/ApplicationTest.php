<?php

namespace OpenCFP\Test;

use OpenCFP\Application;
use OpenCFP\Environment;

/**
 * @covers OpenCFP\Application
 * @group db
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $sut;

    /**
     * @test
     */
    public function it_should_run_and_have_output()
    {
        $this->sut = new Application(BASE_PATH, Environment::testing());
        $this->sut['session.test'] = true;

        // We start an output buffer because the Application sends its response to
        // the output buffer as a Symfony Response.
        ob_start();
        $this->sut->run();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    /** @test */
    public function it_should_resolve_configuration_path_based_on_environment()
    {
        $this->sut = new Application(BASE_PATH, Environment::testing());

        $this->assertTrue($this->sut->isTesting());
        $this->assertContains('testing.yml', $this->sut->configPath());
    }
}
