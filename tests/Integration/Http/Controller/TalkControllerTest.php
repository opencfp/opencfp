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

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkControllerTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private static $user;

    /**
     * @var Talk
     */
    private static $talk;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $talk       = factory(Talk::class, 1)->create()->first();
        self::$user = $talk->speaker->first();
        self::$talk = $talk;
    }

    /**
     * Verify that talks with ampersands and other characters in them can
     * be created and then edited properly
     *
     * @test
     */
    public function ampersandsAcceptableCharacterForTalks()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('edit_talk');
        $response = $this
            ->asLoggedInSpeaker(1)
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'title'       => 'Test Title With Ampersand',
                'description' => 'The title should contain this & that',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'other',
                'desired'     => 0,
                'user_id'     => 1,
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkAfterCFPIsClosed()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsClosed()
            ->post('/talk/create', [
                'token'    => $csrfToken,
                'token_id' => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyNotContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('You cannot create talks once the call for papers has ended', $this->session());
    }

    /**
     * @test
     */
    public function cantProcessCreateTalkWithMissingData()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('speaker_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'description' => 'Talk Description',
                'token'       => $csrfToken,
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Create Your Talk', $response);
        $this->assertSessionHasFlashMessage('Error', $this->session());
    }

    /**
     * @test
     */
    public function processCreateTalkFailsWithBadToken()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->callForPapersIsOpen()
            ->post('/talk/create', [
                'description' => 'Talk Description',
                'token'       => \uniqid(),
                'token_id'    => 'speaker_talk',
            ]);

        $this->assertResponseIsRedirect($response);
        $this->assertRedirectResponseUrlContains('/dashboard', $response);
    }
}
