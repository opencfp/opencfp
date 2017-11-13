<?php

namespace OpenCFP\Test\Http\Controller\Reviewer;

use Mockery;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

class TalksControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $talks;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$talks = factory(Talk::class, 3)->create();
    }

    /**
     * @test
     */
    public function indexActionWorksNormally()
    {
        $this->makeTalks();
        $this->asReviewer()
            ->get('/reviewer/talks')
            ->assertSee('<h2 class="headline">Submitted Talks</h2>')
            ->assertSee('title="I want to see this talk')
            ->assertNotSee('Recent Talks')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function viewActionWillRedirectWhenTalkNotFound()
    {
        $this->asReviewer()
            ->get('/reviewer/talks/255')
            ->assertNotSee('title="I want to see this talk"')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function viewActionWillShowTalk()
    {
        $talk = self::$talks->first();
        $this->asReviewer()
            ->get('/reviewer/talks/'.$talk->id)
            ->assertSee($talk->title)
            ->assertSee($talk->description)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillReturnTrueOnGoodRate()
    {
        $talk = self::$talks->first();
        $this->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', ['rating' => 1])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillNotReturnTrueOnBadRate()
    {
        $talk = self::$talks->first();
        $this->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', ['rating' => 8])
            ->assertNotSee('1')
            ->assertSuccessful();
    }

    protected function makeTalks()
    {
        $formatter = new TalkFormatter();
        $toReturn = $formatter->formatList(self::$talks, 1);
        $filter = Mockery::mock(TalkFilter::class);
        $filter->shouldReceive('getFilteredTalks')->andReturn($toReturn->toArray());
        $this->swap(TalkFilter::class, $filter);
    }
}
