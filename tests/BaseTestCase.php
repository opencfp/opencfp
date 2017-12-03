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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Application;
use OpenCFP\Environment;
use OpenCFP\Test\Helper\DataBaseInteraction;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use OpenCFP\Test\Helper\RefreshDatabase;

abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;
    use MockeryPHPUnitIntegration;

    /**
     * @var Application
     */
    protected $app;

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
            $this->app = null;
        }
    }

    public function createApplication(): Application
    {
        $app                 = new Application(__DIR__ . '/..', Environment::testing());
        $app['session.test'] = true;

        return $app;
    }

    public function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    /**
     * Runs setUps from Traits that are needed before every test (as called from setUp)
     */
    public function runBeforeTestTraits()
    {
        $uses = \array_flip(class_uses_recursive(static::class));

        if (isset($uses[DataBaseInteraction::class])) {
            $this->resetDatabase();
        }
    }

    /**
     * Runs setups from Traits that are needed before the class is setup (as called from setUpBeforeClass)
     */
    protected static function runBeforeClassTraits()
    {
        $uses = \array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            static::setUpDatabase();
        }
    }
}
