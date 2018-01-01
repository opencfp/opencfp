<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
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
    public function testRendersLoginFormIfAuthenticationFailed()
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
}
