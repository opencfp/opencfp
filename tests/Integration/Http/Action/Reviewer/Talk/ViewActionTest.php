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

namespace OpenCFP\Test\Integration\Http\Action\Reviewer\Talk;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ViewActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function viewActionWillRedirectWhenTalkNotFound()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        $id = $this->faker()->numberBetween(100);

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/talks/' . $id);

        $this->assertResponseBodyNotContains('title="I want to see this talk"', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function viewActionWillShowTalk()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        /** @var Model\Talk $talk */
        $talk = factory(Model\Talk::class)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/talks/' . $talk->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($talk->title, $response);
        $this->assertResponseBodyContains($talk->description, $response);
    }
}
