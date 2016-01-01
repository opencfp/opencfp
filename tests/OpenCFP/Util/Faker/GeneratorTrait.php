<?php

namespace OpenCFP\Util\Faker;

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
        }

        return $faker;
    }
}
