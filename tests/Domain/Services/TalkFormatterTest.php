<?php

namespace OpenCFP\Test\Domain\Services;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * @group db
 */
class TalkFormatterTest extends BaseTestCase
{
    use RefreshDatabase;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::generateSomeTalks();
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithNoMeta()
    {
        $talk = new Talk;
        $formatter = new TalkFormatter();

        $format =$formatter->createdFormattedOutput($talk->first(), 1);

        $this->assertEquals('One talk to rule them all', $format['title']);
        $this->assertEquals('api', $format['category']);
        $this->assertEquals(0, $format['meta']->rating);
        $this->assertEquals(0, $format['meta']->viewed);
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithMeta()
    {
        $formatter = new TalkFormatter();
        $talk = new Talk;

        // Now to see if the meta gets put in correctly
        $secondFormat =$formatter->createdFormattedOutput($talk->first(), 2);

        $this->assertEquals(1, $secondFormat['meta']->rating);
        $this->assertEquals(1, $secondFormat['meta']->viewed);
    }

    /**
     * @test
     */
    public function formatListReturnsAllTalksAsCollection()
    {
        $formatter = new TalkFormatter();
        $talks = Talk::all();
        $formatted = $formatter->formatList($talks, 2);
        $this->assertEquals(count($talks), count($formatted));
        $this->assertInstanceOf(Collection::class, $formatted);
    }

    private static function generateSomeTalks()
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
        $talk->create(
            [
                'user_id' => 8,
                'title' => 'Extra Extra',
                'description' => 'Talk',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );
        $talk->create(
            [
                'user_id' => 8,
                'title' => 'Third',
                'description' => 'Talk',
                'type' => 'regular',
                'level' => 'entry',
                'category' => 'api',
            ]
        );
    }
}
