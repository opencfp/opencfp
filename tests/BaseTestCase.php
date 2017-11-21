<?php

namespace OpenCFP\Test;

use Mockery;
use OpenCFP\Application;
use OpenCFP\Environment;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
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

        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }

    public function createApplication()
    {
        $app                 = new Application(BASE_PATH, Environment::testing());
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
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[DataBaseInteraction::class])) {
            $this->resetDatabase();
        }
    }

    /**
     * Runs setups from Traits that are needed before the class is setup (as called from setUpBeforeClass)
     */
    protected static function runBeforeClassTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            static::setUpDatabase();
        }
    }
}
