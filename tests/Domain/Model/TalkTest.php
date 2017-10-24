<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\DatabaseTestCase;

/**
 * @group db
 */
class TalkTest extends DatabaseTestCase
{

    /** @test */
    public function recentReturnsAnArrayOfTalks()
    {
        factory(Talk::class, 10)->create();

        $this->assertCount(10, Talk::recent()->get());
        $this->assertCount(3, Talk::recent(3)->get());
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

    /**
     * Helper function that generates some talks for us
     */
    private function generateTalks()
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

        $talk->create(
            [
                'user_id' => 1,
                'title' => 'My second talk',
                'description' => 'I told you two is fine',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );

        $talk->create(
            [
                'user_id' => 1,
                'title' => 'Third times a charm',
                'description' => 'But you cant be too sure ',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );

        $talk->create(
            [
                'user_id' => 1,
                'title' => 'Lets do one more',
                'description' => 'You know, just in case',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );
    }
}
