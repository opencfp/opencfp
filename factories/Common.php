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

$factory->define(\OpenCFP\Domain\Model\User::class, function (\Faker\Generator $faker) {
    return [
        'email'            => $faker->unique()->safeEmail,
        'password'         => \password_hash('secret', PASSWORD_BCRYPT),
        'activated'        => 1,
        'first_name'       => $faker->firstName,
        'last_name'        => $faker->lastName,
        'company'          => $faker->company,
        'twitter'          => '@' . $faker->userName,
        'joindin_username' => $faker->userName,
        'activated_at'     => $faker->dateTimeInInterval('-2 months', '-1 months'),
        'last_login'       => $faker->dateTimeInInterval('-5 days', 'now'),
        'transportation'   => $faker->randomElement([0, 1]),
        'hotel'            => $faker->randomElement([0, 1]),
        'info'             => $faker->realText(),
        'bio'              => $faker->realText(),
    ];
});

$factory->define(\OpenCFP\Domain\Model\Talk::class, function (\Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(\OpenCFP\Domain\Model\User::class)->create()->id;
        },
        'title'       => $faker->sentence(),
        'description' => $faker->realText(),
        'other'       => $faker->realText(),
        'type'        => $faker->randomElement(['regular', 'tutorial']),
        'level'       => $faker->randomElement(['entry', 'mid', 'advanced']),
        'category'    => $faker->randomElement(['api', 'database', 'development', 'testing']),
    ];
});

$factory->define(\OpenCFP\Domain\Model\TalkMeta::class, function (\Faker\Generator $faker) {
    return [
        'admin_user_id' => factory(\OpenCFP\Domain\Model\User::class)->create()->id,
        'rating'        => 1,
        'viewed'        => 1,
        'talk_id'       => factory(\OpenCFP\Domain\Model\Talk::class)->create()->id,
        'created'       => new \DateTime(),
    ];
});

$factory->define(\OpenCFP\Domain\Model\TalkComment::class, function (\Faker\Generator $faker) {
    return [
        'user_id' => factory(\OpenCFP\Domain\Model\User::class)->create()->id,
        'talk_id' => factory(\OpenCFP\Domain\Model\Talk::class)->create()->id,
        'message' => $faker->realText(),
        'created' => new \DateTime(),
    ];
});

$factory->define(\OpenCFP\Domain\Model\Favorite::class, function (\Faker\Generator $faker) {
    return [
        'admin_user_id' => factory(\OpenCFP\Domain\Model\User::class)->create()->id,
        'talk_id'       => factory(\OpenCFP\Domain\Model\Talk::class)->create()->id,
        'created'       => new \DateTime(),
    ];
});
