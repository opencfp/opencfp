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

namespace OpenCFP\Test\Integration\Http\Action\Signup;

use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ProcessActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function signUpWorksCorrectly()
    {
        $faker = $this->faker();

        $password = $faker->password;

        $response = $this->post('/signup', [
            'first_name'       => $faker->firstName,
            'last_name'        => $faker->lastName,
            'email'            => $faker->email,
            'company'          => null,
            'twitter'          => null,
            'url'              => 'https://example.com',
            'joindin_username' => $faker->userName,
            'password'         => $password,
            'password2'        => $password,
            'airport'          => null,
            'speaker_info'     => null,
            'speaker_bio'      => null,
            'transportation'   => null,
            'hotel'            => null,
            'buttonInfo'       => 'Create my speaker profile',
            'coc'              => 1,
            'privacy'          => 1,
        ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('dashboard', $response);
        $this->assertSessionHasFlashMessage("You've successfully created your account!", $this->session());
    }

    /**
     * @test
     */
    public function signUpWithoutJoindInWorks()
    {
        $faker = $this->faker();

        $password = $faker->password;

        $response = $this->post('/signup', [
            'first_name'       => $faker->firstName,
            'last_name'        => $faker->lastName,
            'email'            => $faker->email,
            'company'          => null,
            'twitter'          => null,
            'url'              => null,
            'joindin_username' => null,
            'password'         => $password,
            'password2'        => $password,
            'airport'          => null,
            'speaker_info'     => null,
            'speaker_bio'      => null,
            'transportation'   => null,
            'hotel'            => null,
            'buttonInfo'       => 'Create my speaker profile',
            'coc'              => 1,
            'privacy'          => 1,
        ]);

        $this->assertSessionHasFlashMessage("You've successfully created your account!", $this->session());
        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('dashboard', $response);
    }
}
