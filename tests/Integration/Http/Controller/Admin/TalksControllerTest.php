<?php

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * @coversNothing
 */
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
    public function indexPageDisplaysTalksCorrectly()
    {
        $this->asAdmin()
            ->get('/admin/talks')
            ->assertSee(self::$talks->first()->title)
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
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post(
                '/admin/talks/' . $talk->id . '/comment',
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
        $talk = self::$talks->first();

        $this->asAdmin()
            ->get('/admin/talks/' . $talk->id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function previouslyViewedTalksDisplaysCorrectly()
    {
        $meta = factory(TalkMeta::class, 1)->create();
        $this->asAdmin($meta->first()->admin_user_id);

        $this->get('/admin/talks/' . $meta->first()->talk_id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionWorksCorrectly()
    {
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/select')
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function selectActionDeletesCorrectly()
    {
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/select', ['delete' => 1])
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
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/favorite')
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function favoriteActionDeletesCorrectly()
    {
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/favorite', ['delete' =>1])
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

    /**
     * @test
     */
    public function rateActionWorksCorrectly()
    {
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', ['rating' => 1])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionRetunsFalseOnWrongRate()
    {
        $talk = self::$talks->first();

        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', ['rating' => 12])
            ->assertNotSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillReturnTrueOnGoodNumericRate()
    {
        $talk = self::$talks->first();
        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', ['rating' => '0'])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillReturnFalseOnNonIntInput()
    {
        $talk = self::$talks->first();
        $this->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', ['rating' => 'blabla'])
            ->assertNotSee('1')
            ->assertSuccessful();
    }
}
