<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

use Illuminate\Database\Eloquent\Factory;

if (!\function_exists('factory')) {
    /**
     * Create a model factory builder for a given class, name, and amount.
     *
     * @param  dynamic  class|class,name|class,amount|class,name,amount
     *
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    function factory()
    {
        $faker = \Faker\Factory::create();

        $factory = Factory::construct($faker, __DIR__ . '/../factories');

        $arguments = \func_get_args();

        if (isset($arguments[1]) && \is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
        }

        if (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        }

        return $factory->of($arguments[0]);
    }
}
