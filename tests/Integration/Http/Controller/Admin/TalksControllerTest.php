<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalksControllerTest extends WebTestCase
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
        $response = $this
            ->asAdmin()
            ->get('/admin/talks');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$talks->first()->title, $response);
    }

    /**
     * @test
     */
    public function indexPageWorkWithNoTalks()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/talks');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Submitted Talks', $response);
    }

    /**
     * A test to make sure that comments can be correctly tracked
     *
     * @test
     */
    public function talkIsCorrectlyCommentedOn()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/comment', [
                'comment' => 'Great Talk i rate 10/10',
            ]);
        
        $this->assertResponseBodyNotContains('Server Error', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * Verify that not found talk redirects
     *
     * @test
     */
    public function talkNotFoundRedirectsBackToTalksOverview()
    {
        $response = $this->get('/admin/talks/255');

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('<strong>Submitted by:</strong>', $response);
    }

    /**
     * @test
     */
    public function talkWithNoMetaDisplaysCorrectly()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->get('/admin/talks/' . $talk->id);

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function previouslyViewedTalksDisplaysCorrectly()
    {
        $meta = factory(TalkMeta::class, 1)->create();

        $response = $this
            ->asAdmin($meta->first()->admin_user_id)
            ->get('/admin/talks/' . $meta->first()->talk_id);

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function selectActionWorksCorrectly()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/select');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function selectActionDeletesCorrectly()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/select', [
                'delete' => 1,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function selectActionReturnsFalseWhenTalkNotFound()
    {
        $response = $this
            ->asAdmin()
            ->post('/admin/talks/255/select');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionWorksCorrectly()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/favorite');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionDeletesCorrectly()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/favorite', [
                'delete' => 1,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('1', $response);
    }

    /**
     * @test
     */
    public function favoriteActionDoesNotErrorWhenTryingToDeleteFavoriteThatDoesNoExist()
    {
        $response = $this
            ->asAdmin()
            ->post('/admin/talks/255/favorite', [
                'delete' => 1,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('1', $response);
        ;
    }

    /**
     * @test
     * @dataProvider providerValidRating
     *
     * @param mixed $rating
     */
    public function rateActionWorksCorrectly($rating)
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodySame('1', $response);
    }

    public function providerValidRating(): array
    {
        return [
            'int' => [
                1,
            ],
            'integerish' => [
                '0',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerInvalidRating
     *
     * @param mixed $rating
     */
    public function rateActionReturnsFalseOnWrongRate($rating)
    {
        $talk = self::$talks->first();

        $response = $this
            ->asAdmin()
            ->post('/admin/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyEmpty($response);
    }

    public function providerInvalidRating(): array
    {
        return [
            'int-too-large' => [
                12,
            ],
            'string' => [
                'blabla',
            ],
        ];
    }
}
