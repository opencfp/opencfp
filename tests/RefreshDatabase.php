<?php

namespace OpenCFP\Test;

use Illuminate\Database\Capsule\Manager as Capsule;

trait RefreshDatabase
{
    protected function setUpDatabase()
    {
        $this->createCapsule()->getConnection()->unprepared(file_get_contents(__DIR__. '/dump.sql'));
    }

    protected function createCapsule()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $this->app->config('database.host'),
            'database'  => $this->app->config('database.database'),
            'username'  => $this->app->config('database.user'),
            'password'  => $this->app->config('database.password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    }
}
