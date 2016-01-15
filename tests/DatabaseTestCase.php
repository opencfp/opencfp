<?php

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class DatabaseTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Make sure to call parent::setUp() if you override this.
     */
    protected function setUp()
    {
        $this->migrate();
    }

    protected function migrate()
    {
        $this->phinx('migrate --environment=testing');
    }

    protected function getCapsule()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'cfp_travis',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();

        return $capsule;
    }

    private function phinx($command)
    {
        return exec(escapeshellcmd(__DIR__ . "/../vendor/bin/phinx $command"));
    }
}