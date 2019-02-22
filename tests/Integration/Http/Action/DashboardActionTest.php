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

namespace OpenCFP\Test\Integration\Http\Action;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Test\Helper\MockAuthentication;
use OpenCFP\Test\Helper\MockIdentityProvider;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class DashboardActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * Test that the index page returns a list of talks associated
     * with a specific user and information about that user as well
     *
     * @test
     */
    public function indexDisplaysUserAndTalks()
    {
        $accounts = $this->container->get(AccountManagement::class);

        $user = $accounts->create('someone@example.com', 'some password', [
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]);
        $accounts->activate($user->getLogin());
        $accounts->promoteTo($user->getLogin(), 'admin');

        Talk::create([
            'title'       => 'Test Title',
            'description' => 'A good one!',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
            'user_id'     => $user->getId(),
        ]);

        /** @var MockAuthentication $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User([
            'id'         => $user->getId(),
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]));

        $this->callForPapersIsOpen();

        $response = $this->get('/dashboard');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Test Title', $response);
        $this->assertResponseBodyContains('Test User', $response);
    }

    /**
     * @test
     */
    public function it_hides_transportation_and_hotel_when_doing_an_online_conference()
    {
        $accounts = $this->container->get(AccountManagement::class);

        $user = $accounts->create('another.one@example.com', 'some password', [
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]);
        $accounts->activate($user->getLogin());

        /** @var MockAuthentication $authentication */
        $authentication = $this->container->get(Authentication::class);
        $authentication->overrideUser($user);

        /** @var MockIdentityProvider $identityProvider */
        $identityProvider = $this->container->get(IdentityProvider::class);
        $identityProvider->overrideCurrentUser(new User([
            'id'         => $user->getId(),
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]));

        $response = $this
            ->callForPapersIsOpen()
            ->isOnlineConference()
            ->get('/dashboard');

        $this->assertResponseBodyNotContains('Need Transportation', $response);
        $this->assertResponseBodyNotContains('Need Hotel', $response);
    }
}
