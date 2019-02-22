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

namespace OpenCFP\Test\Integration\Domain\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkMetaTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function talkRelationWorks()
    {
        /** @var TalkMeta $meta */
        $meta = factory(TalkMeta::class, 1)->create()->first();

        $talk = $meta->talk();

        $this->assertInstanceOf(BelongsTo::class, $talk);
        $this->assertInstanceOf(Talk::class, $talk->first());
    }

    /**
     * @test
     */
    public function userRelationWorks()
    {
        /** @var TalkMeta $meta */
        $meta = factory(TalkMeta::class, 1)->create()->first();

        $user = $meta->user();

        $this->assertInstanceOf(BelongsTo::class, $user);
        $this->assertInstanceOf(User::class, $user->first());
    }
}
