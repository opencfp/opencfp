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

namespace OpenCFP\Test\Integration\Http\Action\Reviewer\Speaker;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class IndexActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function indexActionWorksWithNoSpeakers()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class, 1)->create()->first();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/speakers');

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function indexActionDisplaysSpeakers()
    {
        $count = $this->faker()->numberBetween(3, 5);

        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class, 1)->create()->first();

        /** @var Eloquent\Collection|Model\User[] $speakers */
        $speakers = factory(Model\User::class, $count)->create();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/speakers');

        $this->assertResponseIsSuccessful($response);

        foreach ($speakers as $speaker) {
            $this->assertResponseBodyContains($speaker->first_name, $response);
            $this->assertResponseBodyContains($speaker->last_name, $response);
        }
    }
}
