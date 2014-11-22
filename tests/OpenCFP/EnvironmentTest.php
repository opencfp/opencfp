<?php namespace OpenCFP;

/**
 * @covers OpenCFP\Environment
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase 
{
    /** @test */
    public function it_should_encapsulate_valid_environments()
    {
        $this->assertInstanceOf('OpenCFP\Environment', Environment::production());

        $this->assertEquals('production', Environment::production());
        $this->assertEquals('development', Environment::development());
        $this->assertEquals('testing', Environment::testing());
    }

    /** @test */
    public function it_should_be_resolvable_from_environment_variable()
    {
        $_SERVER['CFP_ENV'] = 'testing';
        $this->assertEquals('testing', Environment::fromEnvironmentVariable());
    }

    /** @test */
    public function it_should_be_resolvable_from_string()
    {
        $this->assertEquals('testing', Environment::fromString('testing'));
    }
}
 