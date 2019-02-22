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

namespace OpenCFP\Test\Integration\Http\Action\Admin\Talk;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class IndexActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function indexPageDisplaysTalksCorrectly()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        /** @var Eloquent\Collection|Model\Talk[] $talks */
        $talks = factory(Model\Talk::class, 3)->create();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/talks');

        $this->assertResponseIsSuccessful($response);

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }

        $this->assertResponseBodyContains('Submitted Talks', $response);
    }

    /**
     * @test
     */
    public function indexPageWorkWithNoTalks()
    {
        /** @var Model\User $admin */
        $admin = factory(Model\User::class)->create()->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/talks');

        $this->assertResponseIsSuccessful($response);
    }
}
