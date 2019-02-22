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

namespace OpenCFP\Test\Integration\Http\Action\Reviewer;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class DashboardActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function indexDisplaysListOfTalks()
    {
        /** @var Model\User[] $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        /** @var Eloquent\Collection|Model\Talk[] $talks */
        $talks = factory(Model\Talk::class, 2)->create();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/');

        $this->assertResponseIsSuccessful($response);

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }

        $this->assertSessionHasNoFlashMessage($this->session());
    }
}
