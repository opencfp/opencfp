<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * @group db
 */
class TalkTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    public function recentReturnsAnArrayOfTalks()
    {
        factory(Talk::class, 3)->create();

        $this->assertCount(3, Talk::recent()->get());
        $this->assertCount(2, Talk::recent(2)->get());
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithNoMeta()
    {
        $this->generateOneTalkWithoutMeta();
        $talk = new Talk;
        $formatter = new TalkFormatter();
        $format =$formatter->createdFormattedOutput($talk->first(), 1);

        $this->assertEquals('This talk has no meta', $format['title']);
        $this->assertEquals('api', $format['category']);
        $this->assertEquals(0, $format['meta']->rating);
        $this->assertEquals(0, $format['meta']->viewed);
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithMeta()
    {
        $this->generateOneTalk();
        $talk = new Talk;

        // Now to see if the meta gets put in correctly
        $talkFormatter = new TalkFormatter();
        $secondFormat =$talkFormatter->createdFormattedOutput($talk->first(), 2);

        $this->assertEquals(1, $secondFormat['meta']->rating);
        $this->assertEquals(1, $secondFormat['meta']->viewed);
    }

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
        $talk = $comment->talk()->first();

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
        $talk = $comment->talk()->first();

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
        $talk = $favorite->talk()->first();

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
        $talk = $favorite->talk()->first();

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

    private function generateOneTalk()
    {
        $talk = new Talk();

        $talk->create(
            [
                'user_id' => 1,
                'title' => 'One talk to rule them all',
                'description' => 'Two is fine too',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );

        $meta = new TalkMeta();
        $meta->create(
            [
                'admin_user_id' => 2,
                'rating' => 1,
                'viewed' => 1,
                'talk_id' => $talk->first()->id,
                'created' => new \DateTime(),
            ]
        );
    }

    private function generateOneTalkWithoutMeta()
    {
        $talk = new Talk;
        $talk->create(
            [
                'user_id' => 1,
                'title' => 'This talk has no meta',
                'description' => 'A talk without meta is so meta',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );
    }
}
