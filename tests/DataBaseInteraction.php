<?php

namespace OpenCFP\Test;

use Illuminate\Database\Capsule\Manager as Capsule;

trait DataBaseInteraction
{
    protected function resetDatabase()
    {
        $this->createCapsule()->getConnection()->unprepared(file_get_contents(__DIR__. '/dump.sql'));
    }

    protected function createCapsule()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'cfp_test',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }
}
