<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Helper\Faker;

use Faker\Factory;
use Faker\Generator;

trait GeneratorTrait
{
    protected function getFaker(): Generator
    {
        static $faker;

        if ($faker === null) {
            $faker = Factory::create();
            $faker->seed(9000);
        }

        return $faker;
    }
}
