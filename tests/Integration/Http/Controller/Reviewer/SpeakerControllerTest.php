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
use OpenCFP\Test\WebTestCase;

/**
 * @coversNothing
 */
class SpeakerControllerTest extends WebTestCase
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
        $this->asReviewer()
            ->get('/reviewer/speakers')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexActionDisplaysSpeakers()
    {
        $speaker = self::$users->first();
        $this->asReviewer()
            ->get('/reviewer/speakers')
            ->assertSee($speaker->first_name)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function viewActionRedirectsWhenUserDoesntExist()
    {
        $this->asReviewer()
            ->get('/reviewer/speakers/255')
            ->assertNotSee('Speaker Bio')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function viewActionShowsSpeaker()
    {
        $speaker = self::$users->first();

        $this->asReviewer()
            ->get('/reviewer/speakers/' . $speaker->id)
            ->assertSee($speaker->first_name)
            ->assertSee($speaker->bio)
            ->assertSuccessful();
    }
}
