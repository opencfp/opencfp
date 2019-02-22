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

use Cartalyst\Support\Collection;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class IndexActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function indexActionWorksNormally()
    {
        /** @var Model\User $reviewer */
        $reviewer = factory(Model\User::class)->create()->first();

        /** @var Collection|Model\Talk[] $talks */
        $talks = factory(Model\Talk::class, 3)->create();

        $response = $this
            ->asReviewer($reviewer->id)
            ->get('/reviewer/talks');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('<h2 class="headline">Submitted Talks</h2>', $response);

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }

        $this->assertResponseBodyNotContains('Recent Talks', $response);
    }
}
