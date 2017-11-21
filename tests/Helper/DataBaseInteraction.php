<?php

namespace OpenCFP\Test\Helper;

use Illuminate\Database\Capsule\Manager as Capsule;

trait DataBaseInteraction
{
    protected function resetDatabase()
    {
        $this->getCapsule()->getConnection()->unprepared(file_get_contents(__DIR__ . '/../dump.sql'));
    }

    protected function getCapsule()
    {
        return $this->app[Capsule::class];
    }
}
