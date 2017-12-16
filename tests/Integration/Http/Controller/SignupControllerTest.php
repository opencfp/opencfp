<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class SignupControllerTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function signupAfterEnddateShowsError()
    {
        $response = $this
            ->callForPapersIsClosed()
            ->get('/signup');

        $this->assertResponseBodyNotContains('Signup', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function signupBeforeEnddateRendersSignupForm()
    {
        $response = $this
            ->callForPapersIsOpen()
            ->get('/signup');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Signup', $response);
    }

    /**
     * @test
     */
    public function signUpRedirectsWhenLoggedIn()
    {
        $response = $this
            ->asAdmin()
            ->get('/signup');

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Signup', $response);
    }

    /**
     * @test
     */
    public function signUpWorksCorrectly()
    {
        $response = $this->post('/signup', [
            'first_name'     => 'Testy',
            'last_name'      => 'McTesterton',
            'email'          => 'test@opencfp.org',
            'company'        => null,
            'twitter'        => null,
            'url'            => 'https://joind.in/user/abc123',
            'password'       => 'wutwut',
            'password2'      => 'wutwut',
            'airport'        => null,
            'speaker_info'   => null,
            'speaker_bio'    => null,
            'transportation' => null,
            'hotel'          => null,
            'buttonInfo'     => 'Create my speaker profile',
            'coc'            => 1,
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
        $response = $this->post('/signup', [
            'first_name'     => 'Testy',
            'last_name'      => 'McTesterton',
            'email'          => 'test@example.org',
            'company'        => null,
            'twitter'        => null,
            'url'            => null,
            'password'       => 'wutwut',
            'password2'      => 'wutwut',
            'airport'        => null,
            'speaker_info'   => null,
            'speaker_bio'    => null,
            'transportation' => null,
            'hotel'          => null,
            'buttonInfo'     => 'Create my speaker profile',
            'coc'            => 1,
        ]);

        $this->assertSessionHasFlashMessage("You've successfully created your account!", $this->session());
        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('dashboard', $response);
    }
}
