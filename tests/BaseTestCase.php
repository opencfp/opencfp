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

namespace OpenCFP\Test;

use Localheinz\Test\Util\Helper;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Application;
use OpenCFP\Environment;
use OpenCFP\Test\Helper\DataBaseInteraction;
use OpenCFP\Test\Helper\RefreshDatabase;
use Pimple\Psr11\Container;
use Psr\Container\ContainerInterface;

abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use MockeryPHPUnitIntegration;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static function setUpBeforeClass()
    {
        self::runBeforeClassTraits();
    }

    protected function setUp()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }
        $this->runBeforeTestTraits();
    }

    protected function tearDown()
    {
        if ($this->app) {
            $this->app->flush();
            $this->app       = null;
            $this->container = null;
        }
    }

    private function createApplication(): Application
    {
        return new Application(__DIR__ . '/..', Environment::testing());
    }

    private function refreshApplication()
    {
        $this->app       = $this->createApplication();
        $this->container = new Container($this->app);
    }

    /**
     * Runs setUps from Traits that are needed before every test (as called from setUp)
     */
    private function runBeforeTestTraits()
    {
        $uses = \array_flip(class_uses_recursive(static::class));

        if (isset($uses[DataBaseInteraction::class])) {
            $this->resetDatabase();
        }
    }

    /**
     * Runs setups from Traits that are needed before the class is setup (as called from setUpBeforeClass)
     */
    private static function runBeforeClassTraits()
    {
        $uses = \array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            static::setUpDatabase();
        }
    }
}
