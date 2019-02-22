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

namespace OpenCFP\Test\Integration\Domain\Model\Interaction;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function deleteWorksWithMeta()
    {
        /** @var TalkMeta $meta */
        $meta = factory(TalkMeta::class, 1)->create()->first();
        $talk = $meta->talk()->first();

        $talk->delete();
        $this->assertCount(0, TalkMeta::all());
    }

    /**
     * @test
     */
    public function deleteMetaButKeepTalkIsPossible()
    {
        /** @var TalkMeta $meta */
        $meta = factory(TalkMeta::class, 1)->create()->first();
        $talk = $meta->talk()->first();

        $talk->deleteMeta();
        $this->assertCount(0, TalkMeta::all());
        $this->assertCount(1, Talk::all());
    }

    /**
     * @test
     */
    public function deleteWorksWithComments()
    {
        /** @var TalkComment $meta */
        $comment = factory(TalkComment::class, 1)->create()->first();
        $talk    = $comment->talk()->first();

        $talk->delete();
        $this->assertCount(0, TalkComment::all());
    }

    /**
     * @test
     */
    public function deleteCommentsButKeepTalkIsPossible()
    {
        /** @var TalkComment $comment */
        $comment = factory(TalkComment::class, 1)->create()->first();
        $talk    = $comment->talk()->first();

        $talk->deleteComments();
        $this->assertCount(0, TalkComment::all());
        $this->assertCount(1, Talk::all());
    }

    /**
     * @test
     */
    public function deleteWorksWithFavorites()
    {
        /** @var Favorite $favorite */
        $favorite = factory(Favorite::class, 1)->create()->first();
        $talk     = $favorite->talk()->first();

        $talk->delete();
        $this->assertCount(0, Favorite::all());
    }

    /**
     * @test
     */
    public function deleteFavoritesButKeepTalkIsPossible()
    {
        /** @var Favorite $favorite */
        $favorite = factory(Favorite::class, 1)->create()->first();
        $talk     = $favorite->talk()->first();

        $talk->deleteFavorites();
        $this->assertCount(0, Favorite::all());
        $this->assertCount(1, Talk::all());
    }

    /**
     * @test
     */
    public function deleteWorksWithNoOtherItems()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->assertTrue($talk->delete());
    }
}
