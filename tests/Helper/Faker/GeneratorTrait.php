<?php

namespace OpenCFP\Test\Helper\Faker;

use Faker\Factory;
use Faker\Generator;

trait GeneratorTrait
{
    /**
     * @return Generator
     */
    protected function getFaker()
    {
        static $faker;

        if ($faker === null) {
            $faker = Factory::create();
            $faker->seed(9000);
        }

        return $faker;
    }
}
