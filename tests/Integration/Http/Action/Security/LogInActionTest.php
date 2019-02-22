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

namespace OpenCFP\Test\Integration\Http\Action\Security;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

final class LogInActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function rendersLoginFormIfAuthenticationFailed()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $response = $this
            ->post('/login', [
                'email'    => $user->email,
                'password' => $this->faker()->password,
            ]);

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_BAD_REQUEST, $response);
        $this->assertResponseBodyContains($user->email, $response);
        $this->assertResponseBodyContains('Email', $response);
        $this->assertResponseBodyContains('Password', $response);
        $this->assertResponseBodyContains('Login', $response);
    }

    /**
     * @test
     */
    public function rendersLoginFormIfInvalidEmail()
    {
        $response = $this
            ->post('/login', [
                'email'    => $this->faker()->password,
                'password' => $this->faker()->password,
            ]);

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_BAD_REQUEST, $response);
        $this->assertResponseBodyContains('Email', $response);
        $this->assertResponseBodyContains('Password', $response);
        $this->assertResponseBodyContains('Login', $response);
    }

    /**
     * @test
     */
    public function rendersSignInFormIfEmailDoesNotExist()
    {
        $randomEmail = $this->faker()->unique()->email;
        $response    = $this
            ->post('/login', [
                'email'    => $randomEmail,
                'password' => $this->faker()->password,
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlEquals('/signup', $response);
        $this->assertSessionHasFlashMessage('User does not exist in the system; you can sign up below!', $this->session());
    }
}
