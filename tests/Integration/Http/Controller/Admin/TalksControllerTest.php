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
}
