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

trait DataBaseInteraction
{
    protected function resetDatabase()
    {
        $this->getCapsule()->getConnection()->unprepared(\file_get_contents(__DIR__ . '/../dump.sql'));
    }

    protected function getCapsule(): Manager
    {
        return $this->app[Manager::class];
    }
}
