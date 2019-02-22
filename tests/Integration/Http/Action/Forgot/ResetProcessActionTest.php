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

namespace OpenCFP\Test\Integration\Http\Action\Forgot;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;
use Symfony\Component\HttpFoundation;

final class ResetProcessActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function rendersResetPasswordFormIfFormIsNotSubmitted()
    {
        $resetCode = $this->faker()->sha256;

        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $client = $this->createClient();

        $client->request(HttpFoundation\Request::METHOD_GET, \sprintf(
            '/reset/%s/%s',
            $user->id,
            $resetCode
        ));

        $response = $client->getResponse();

        $this->assertResponseStatusCode(HttpFoundation\Response::HTTP_OK, $response);
        $this->assertResponseBodyContains('<h2 class="headline">Reset My Password</h2>', $response);
        $this->assertResponseBodyContains('<button type="submit" class="btn btn-success" >Change my password</button>', $response);
    }
}
