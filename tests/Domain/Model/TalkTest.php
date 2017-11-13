<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Talk;
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

    private static $talks;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $talks = factory(Talk::class, 2)->create();
        factory(TalkMeta::class, 1)->create(['admin_user_id' => 2, 'talk_id' => $talks->first()->id]);
        self::$talks = $talks;
    }

    /** @test */
    public function recentReturnsAnArrayOfTalks()
    {
        $this->assertCount(3, Talk::recent()->get());
        $this->assertCount(2, Talk::recent(2)->get());
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithNoMeta()
    {
        $talk = self::$talks;
        $formatter = new TalkFormatter();
        $format =$formatter->createdFormattedOutput($talk->first(), 1);

        $this->assertEquals(self::$talks->first()->title, $format['title']);
        $this->assertEquals(0, $format['meta']->rating);
        $this->assertEquals(0, $format['meta']->viewed);
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithMeta()
    {
        $talk = self::$talks;

        // Now to see if the meta gets put in correctly
        $talkFormatter = new TalkFormatter();
        $secondFormat =$talkFormatter->createdFormattedOutput($talk->first(), 2);

        $this->assertEquals(1, $secondFormat['meta']->rating);
        $this->assertEquals(1, $secondFormat['meta']->viewed);
    }
}
