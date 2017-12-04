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
use OpenCFP\Test\WebTestCase;

/**
 * @coversNothing
 */
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
    public function indexActionWorksNormally()
    {
        $this->asReviewer()
            ->get('/reviewer/talks')
            ->assertSee('<h2 class="headline">Submitted Talks</h2>')
            ->assertSee(self::$talks->first()->title)
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
            ->get('/reviewer/talks/' . $talk->id)
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
            ->post('/reviewer/talks/' . $talk->id . '/rate', ['rating' => 12])
            ->assertNotSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillReturnTrueOnGoodNumericRate()
    {
        $talk = self::$talks->first();
        $this->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', ['rating' => '0'])
            ->assertSee('1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function rateActionWillReturnFalseOnNonIntInput()
    {
        $talk = self::$talks->first();
        $this->asReviewer()
            ->post('/reviewer/talks/' . $talk->id . '/rate', ['rating' => 'blabla'])
            ->assertNotSee('1')
            ->assertSuccessful();
    }
}
