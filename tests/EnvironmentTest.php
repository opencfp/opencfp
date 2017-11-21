<?php

namespace OpenCFP\Test;

use OpenCFP\Environment;

/**
 * @covers \OpenCFP\Environment
 */
class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_should_encapsulate_valid_environments()
    {
        $this->assertInstanceOf(Environment::class, Environment::production());

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

    /**
     * @test
     */
    public function it_fails_when_given_an_invalid_environment_string()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::fromString('foo');
    }

    /**
     * @test
     */
    public function isProductionReturnsCorrectBool()
    {
        $prod = Environment::production();
        $this->assertTrue($prod->isProduction());
        $dev = Environment::development();
        $this->assertFalse($dev->isProduction());
        $test = Environment::testing();
        $this->assertFalse($test->isProduction());
    }

    /**
     * @test
     */
    public function isDevelopmentReturnsCorrectBool()
    {
        $prod = Environment::production();
        $this->assertFalse($prod->isDevelopment());
        $dev = Environment::development();
        $this->assertTrue($dev->isDevelopment());
        $test = Environment::testing();
        $this->assertFalse($test->isDevelopment());
    }

    /**
     * @test
     */
    public function isTestingReturnsCorrectBool()
    {
        $prod = Environment::production();
        $this->assertFalse($prod->isTesting());
        $dev = Environment::development();
        $this->assertFalse($dev->isTesting());
        $test = Environment::testing();
        $this->assertTrue($test->isTesting());
    }
}
