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

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\WebTestCase;

final class IndexActionTest extends WebTestCase
{
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
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/signup');

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Signup', $response);
    }
}
