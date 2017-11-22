<?php

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
