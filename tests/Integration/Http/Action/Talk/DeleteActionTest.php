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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class DeleteActionTest extends WebTestCase
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
     * @test
     */
    public function notAllowedToDeleteAfterCFPIsOver()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker(self::$user->id)
            ->callForPapersIsClosed()
            ->post('/talk/delete', [
                'tid'      => self::$talk->id,
                'token'    => $csrfToken,
                'token_id' => 'delete_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('ok', $response);
        $this->assertResponseBodyContains('no', $response);
    }

    /**
     * @test
     */
    public function notAllowedToDeleteSomeoneElseTalk()
    {
        $csrfToken = $this->container->get('csrf.token_manager')
            ->getToken('delete_talk')
            ->getValue();

        $response = $this
            ->asLoggedInSpeaker(self::$user->id + 1)
            ->post('/talk/delete', [
                'tid'      => self::$talk->id,
                'token'    => $csrfToken,
                'token_id' => 'delete_talk',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyNotContains('ok', $response);
        $this->assertResponseBodyContains('no', $response);
    }
}
