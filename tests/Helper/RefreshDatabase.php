<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Helper;

use Illuminate\Database\Capsule\Manager;

trait RefreshDatabase
{
    protected static function setUpDatabase()
    {
        self::createCapsule()->getConnection()->unprepared(\file_get_contents(__DIR__ . '/../dump.sql'));
    }

    protected static function createCapsule(): Manager
    {
        $capsule = new Manager();

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
