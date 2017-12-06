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

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

/**
 * @coversNothing
 */
final class SpeakersControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $users;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$users = factory(User::class, 2)->create();
    }

    /**
     * @test
     */
    public function indexActionWorksWithNoSpeakers()
    {
        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers');

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function indexActionDisplaysSpeakers()
    {
        $speaker = self::$users->first();

        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($speaker->first_name, $response);
    }

    /**
     * @test
     */
    public function viewActionRedirectsWhenUserDoesntExist()
    {
        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers/255');

        $this->assertResponseBodyNotContains('Speaker Bio', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function viewActionShowsSpeaker()
    {
        $speaker = self::$users->first();

        $response = $this
            ->asReviewer()
            ->get('/reviewer/speakers/' . $speaker->id);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($speaker->first_name, $response);
        $this->assertResponseBodyContains($speaker->bio, $response);
    }
}
