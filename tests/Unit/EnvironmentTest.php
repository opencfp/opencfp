<?php

namespace OpenCFP\Test\Unit;

use OpenCFP\Environment;

/**
 * @covers \OpenCFP\Environment
 */
class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    public function testConstants()
    {
        $this->assertSame('production', Environment::TYPE_PRODUCTION);
        $this->assertSame('development', Environment::TYPE_DEVELOPMENT);
        $this->assertSame('testing', Environment::TYPE_TESTING);
    }

    /** @test */
    public function it_should_encapsulate_valid_environments()
    {
        $this->assertInstanceOf(Environment::class, Environment::production());

        $this->assertEquals(Environment::TYPE_PRODUCTION, Environment::production());
        $this->assertEquals(Environment::TYPE_DEVELOPMENT, Environment::development());
        $this->assertEquals(Environment::TYPE_TESTING, Environment::testing());
    }

    /**
     * @test
     * @dataProvider providerEnvironment
     *
     * @param string $type
     */
    public function it_should_be_resolvable_from_environment_variable(string $type)
    {
        $_SERVER['CFP_ENV'] = $type;

        $environment = Environment::fromEnvironmentVariable();

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertEquals($type, $environment);
    }

    /**
     * @test
     * @dataProvider providerEnvironment
     *
     * @param string $type
     */
    public function it_should_be_resolvable_from_string(string $type)
    {
        $environment = Environment::fromString($type);

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertEquals($type, $environment);
    }

    public function providerEnvironment(): \Generator
    {
        $types = [
            Environment::TYPE_DEVELOPMENT,
            Environment::TYPE_PRODUCTION,
            Environment::TYPE_TESTING,
        ];

        foreach ($types as $type) {
            yield $type => [
                $type,
            ];
        }
    }

    /**
     * @test
     */
    public function it_fails_when_given_an_invalid_environment_string()
    {
        $type = 'foo';

        $types = [
            Environment::TYPE_PRODUCTION,
            Environment::TYPE_DEVELOPMENT,
            Environment::TYPE_TESTING,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Environment needs to be one of "%s"; got "%s" instead.',
            implode('", "', $types),
            $type
        ));

        Environment::fromString($type);
    }

    public function testEqualsReturnsFalseIfTypeIsDifferent()
    {
        $one = Environment::fromString(Environment::TYPE_TESTING);
        $two = Environment::fromString(Environment::TYPE_DEVELOPMENT);

        $this->assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueIfTypeIsSame()
    {
        $one = Environment::fromString(Environment::TYPE_TESTING);
        $two = Environment::fromString(Environment::TYPE_TESTING);

        $this->assertTrue($one->equals($two));
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
