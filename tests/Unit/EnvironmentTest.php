<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit;

use OpenCFP\Environment;

final class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constants()
    {
        $this->assertSame('production', Environment::TYPE_PRODUCTION);
        $this->assertSame('development', Environment::TYPE_DEVELOPMENT);
        $this->assertSame('testing', Environment::TYPE_TESTING);
    }

    /**
     * @test
     */
    public function productionReturnsEnvironment()
    {
        $environment = Environment::production();

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertTrue($environment->isProduction());
        $this->assertFalse($environment->isDevelopment());
        $this->assertFalse($environment->isTesting());
        $this->assertSame(Environment::TYPE_PRODUCTION, (string) $environment);
    }

    /**
     * @test
     */
    public function developmentReturnsEnvironment()
    {
        $environment = Environment::development();

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertFalse($environment->isProduction());
        $this->assertTrue($environment->isDevelopment());
        $this->assertFalse($environment->isTesting());
        $this->assertSame(Environment::TYPE_DEVELOPMENT, (string) $environment);
    }

    /**
     * @test
     */
    public function testingReturnsEnvironment()
    {
        $environment = Environment::testing();

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertFalse($environment->isProduction());
        $this->assertFalse($environment->isDevelopment());
        $this->assertTrue($environment->isTesting());
        $this->assertSame(Environment::TYPE_TESTING, (string) $environment);
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
        $this->assertSame($type, (string) $environment);
    }

    /**
     * @dataProvider providerEnvironment
     *
     * @param string $type
     *
     * @test
     */
    public function fromServerReturnsEnvironment(string $type)
    {
        $environment = Environment::fromServer([
            'CFP_ENV' => $type,
        ]);

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertSame($type, (string) $environment);
    }

    /**
     * @test
     */
    public function fromServerDefaultsToProduction()
    {
        $environment = Environment::fromServer([]);

        $this->assertInstanceOf(Environment::class, $environment);
        $this->assertTrue($environment->isProduction());
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
        $this->assertSame($type, (string) $environment);
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
        $this->expectExceptionMessage(\sprintf(
            'Environment needs to be one of "%s"; got "%s" instead.',
            \implode('", "', $types),
            $type
        ));

        Environment::fromString($type);
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfTypeIsDifferent()
    {
        $one = Environment::fromString(Environment::TYPE_TESTING);
        $two = Environment::fromString(Environment::TYPE_DEVELOPMENT);

        $this->assertFalse($one->equals($two));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfTypeIsSame()
    {
        $one = Environment::fromString(Environment::TYPE_TESTING);
        $two = Environment::fromString(Environment::TYPE_TESTING);

        $this->assertTrue($one->equals($two));
    }
}
