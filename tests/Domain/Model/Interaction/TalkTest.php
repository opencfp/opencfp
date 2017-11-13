<?php

namespace OpenCFP\Test\Domain\Model\Interaction;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\DataBaseInteraction;

class TalkTest extends BaseTestCase
{
    use DataBaseInteraction;

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

    /**
     * @test
     */
    public function favoritedByOnlyReturnsFavoritedTalks()
    {
        factory(Favorite::class, 2)->create(['admin_user_id' => 8]);
        factory(Talk::class, 2)->create();
        $favoritedBy = Talk::favoritedBy(8)->get();
        $this->assertCount(2, $favoritedBy);
        $favoritedByOther = Talk::favoritedBy(5)->get();
        $this->assertCount(0, $favoritedByOther);
    }

    /**
     * @test
     */
    public function ratedPlusOneByOnlyReturnsPlusOneRatedTalks()
    {
        factory(TalkMeta::class, 1)->create(['admin_user_id' => 8, 'rating' => 1]);
        factory(TalkMeta::class, 1)->create(['admin_user_id' => 8, 'rating' => 0]);
        factory(TalkMeta::class, 1)->create(['admin_user_id' => 8, 'rating' => -1]);

        $ratedPlusOneBy = Talk::ratedPlusOneBy(8)->get();
        $this->assertCount(1, $ratedPlusOneBy);
        $ratedPlusOneByOther = Talk::ratedPlusOneBy(2)->get();
        $this->assertCount(0, $ratedPlusOneByOther);
    }
}
