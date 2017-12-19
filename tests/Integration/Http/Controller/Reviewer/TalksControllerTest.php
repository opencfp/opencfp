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

namespace OpenCFP\Test\Integration\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\Talk;
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
    public function viewActionWillRedirectWhenTalkNotFound()
    {
        $response = $this
            ->asReviewer()
            ->get('/reviewer/talks/255');

        $this->assertResponseBodyNotContains('title="I want to see this talk"', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function viewActionWillShowTalk()
    {
        $talk = self::$talks->first();

        $response = $this
            ->asReviewer()
            ->get('/reviewer/talks/' . $talk->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($talk->title, $response);
        $this->assertResponseBodyContains($talk->description, $response);
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
            ->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);

        $this->assertSame('1', $response->getContent());
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
            ->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', [
                'rating' => $rating,
            ]);

        $this->assertResponseIsSuccessful($response);

        $this->assertSame('', $response->getContent());
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
