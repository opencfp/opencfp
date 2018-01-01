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

namespace OpenCFP\Test\Integration\Http\Action\Reviewer\Speaker;

use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ViewActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function viewActionRedirectsWhenUserDoesntExist()
    {
        $id = $this->faker()->numberBetween(1);

        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers/' . $id);

        $this->assertResponseBodyNotContains('Speaker Bio', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function viewActionShowsSpeaker()
    {
        /** @var Model\User $speaker */
        $speaker = factory(Model\User::class, 1)->create()->first();

        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers/' . $speaker->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($speaker->first_name, $response);
        $this->assertResponseBodyContains($speaker->bio, $response);
    }
}
