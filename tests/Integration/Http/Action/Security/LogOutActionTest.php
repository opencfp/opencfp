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
use OpenCFP\Domain\Services;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class LogOutActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function logsOutUserAndRedirectsToHomepage()
    {
        /** @var Model\User $user */
        $user = factory(Model\User::class)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->get('/logout');

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlEquals('/', $response);

        /** @var Services\Authentication $authentication */
        $authentication = $this->container->get(Services\Authentication::class);

        $this->assertFalse($authentication->isAuthenticated());
    }
}
