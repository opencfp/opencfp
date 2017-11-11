<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Mockery;
use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\WebTestCase;

class TalksControllerTest extends WebTestCase
{
    use DatabaseTransaction;

    public function setUp()
    {
        parent::setUp();
        $this->asAdmin();
        $this->setUpDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }

    /**
     * Test that the index page grabs a collection of talks
     * and successfully displays them
     *
     * @test
     */
    public function indexPageDisplaysTalksCorrectly()
    {
        $talks = factory(Talk::class, 10)->create();
        $formatter = new TalkFormatter();
        $formatted = $formatter->formatList($talks, 1);
        $filter = Mockery::mock(TalkFilter::class);
        $filter->shouldReceive('getFilteredTalks')
            ->andReturn($formatted->toArray());
        $this->swap(TalkFilter::class, $filter);

        $this->asAdmin()
            ->get('/admin/talks')
            ->assertSee($talks->first()->title)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexPageWorkWithNoTalks()
    {
        $this->asAdmin()
            ->get('/admin/talks')
            ->assertSee('Submitted Talks')
            ->assertSuccessful();
    }

    /**
     * A test to make sure that comments can be correctly tracked
     *
     * @test
     */
    public function talkIsCorrectlyCommentedOn()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post(
                '/admin/talks/'. $talk->id.'/comment',
                ['comment' => 'Great Talk i rate 10/10']
            )
            ->assertNotSee('Server Error')
            ->assertRedirect();
    }

    /**
     * Verify that not found talk redirects
     *
     * @test
     */
    public function talkNotFoundRedirectsBackToTalksOverview()
    {
        $this->get('/admin/talks/255')
            ->assertRedirect()
            ->assertNotSee('<strong>Submitted by:</strong>');
    }

    /**
     * @test
     */
    public function talkWithNoMetaDisplaysCorrectly()
    {
        $talk = factory(Talk::class, 1)->create();

        $this->get('/admin/talks/'. $talk->first()->id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function previouslyViewedTalksDisplaysCorrectly()
    {
        $meta = factory(TalkMeta::class, 1)->create();
        $this->asAdmin($meta->first()->admin_user_id);

        $this->get('/admin/talks/'. $meta->first()->talk_id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionWorksCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id. '/select')
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionDeletesCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id. '/select', ['delete' => 1])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionReturnsFalseWhenTalkNotFound()
    {
        $this->asAdmin()
            ->post('/admin/talks/255/select')
            ->assertNotSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function favoriteActionWorksCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id . '/favorite')
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function favoriteActionDeletesCorrectly()
    {
        $talk = factory(Talk::class, 1)->create()->first();
        Favorite::create([
            'admin_user_id' => 1,
            'talk_id' => $talk->id,
        ]);

        $this->asAdmin()
            ->post('/admin/talks/'. $talk->id . '/favorite', ['delete' =>1])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function favoriteActionDoesNotErrorWhenTryingToDeleteFavoriteThatDoesNoExist()
    {
        $this->asAdmin()
            ->post('/admin/talks/255/favorite', ['delete' => 1])
            ->assertNotSee('1')
            ->assertSuccessful();
    }
}
