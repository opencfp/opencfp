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

    public function setUp()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->runTraits();
    }

    public function tearDown()
    {
        if ($this->app) {
            $this->app->flush();
            $this->app = null;
        }

        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }

    protected function runTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            $this->setUpDatabase();
        }
    }

    public function createApplication()
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session.test'] = true;
        return $app;
    }

    public function refreshApplication()
    {
        $this->app = $this->createApplication();
    }
}
