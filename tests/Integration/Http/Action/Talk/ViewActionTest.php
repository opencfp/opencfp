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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ViewActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function willDisplayOwnTalk()
    {
        $accounts = $this->container->get(AccountManagement::class);

        $user = $accounts->create('someone@example.com', 'some password');
        $accounts->activate($user->getLogin());

        $talk = Model\Talk::create([
            'title'       => 'Some Talk',
            'description' => 'A good one!',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
            'user_id'     => $user->getId(),
        ]);

        $url = \sprintf(
            '/talk/%d',
            $talk->id
        );

        $response = $this
            ->asLoggedInSpeaker($user->getId())
            ->get($url);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($talk->title, $response);
    }
}
