<?php

namespace OpenCFP\Test\Http\Controller;

use OpenCFP\Test\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * @covers \OpenCFP\Http\Controller\SignupController
 * @covers \OpenCFP\Http\Controller\BaseController
 * @group db
 */
class SignupControllerTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function signupAfterEnddateShowsError()
    {
        $this->callForPapersIsClosed()->get('/signup')
            ->assertRedirect()
            ->assertNotSee('Signup');
    }

    /**
     * @test
     */
    public function signupBeforeEnddateRendersSignupForm()
    {
        $this->callForPapersIsOpen()->get('/signup')
            ->assertSuccessful()
            ->assertSee('Signup');
    }

    /**
     * @test
     */
    public function signUpRedirectsWhenLoggedIn()
    {
        $this->asAdmin()->get('/signup')
            ->assertRedirect()
            ->assertNotSee('Signup');
    }

    /**
     * @test
     */
    public function signUpWorksCorrectly()
    {
        // We need to set up our speaker information
        $form_data = [
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
        ];
        $this->post('/signup', $form_data)
            ->assertFlashContains("You've successfully created your account!")
            ->assertRedirect()
            ->assertTargetURLContains('dashboard');
    }

    /**
     * @test
     */
    public function signUpWithoutJoindInWorks()
    {
        // We need to set up our speaker information
        $form_data = [
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
        ];
        $this->post('/signup', $form_data)
            ->assertFlashContains("You've successfully created your account!")
            ->assertRedirect()
            ->assertTargetURLContains('dashboard');
    }
}
