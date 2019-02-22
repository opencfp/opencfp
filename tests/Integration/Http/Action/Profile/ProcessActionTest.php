<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Integration\Http\Action\Profile;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ProcessActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        /** @var User $otherSpeaker */
        $otherSpeaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id' => $otherSpeaker->id,
            ]);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id'         => $speaker->id,
                'email'      => $this->faker()->word,
                'first_name' => 'First',
                'last_name'  => 'Last',
            ]);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('My Profile', $response);
        $this->assertResponseBodyContains('Invalid email address format', $response);
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post('/profile/edit', [
                'id'         => $speaker->id,
                'email'      => $this->faker()->email,
                'first_name' => 'First',
                'last_name'  => 'Last',
            ]);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function speakerPhotoUploadIsProcessed()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $tmpFile  = \tmpfile();
        $tmpPath  = \stream_get_meta_data($tmpFile)['uri'];
        $filedata = \file_get_contents(__DIR__ . '/../../../../../web/assets/img/dummyphoto.jpg');
        \fwrite($tmpFile, $filedata);

        $response = $this
            ->asLoggedInSpeaker($speaker->id)
            ->post(
                '/profile/edit',
                [
                    'id'         => $speaker->id,
                    'email'      => $this->faker()->email,
                    'first_name' => 'First',
                    'last_name'  => 'Last',
                ],
                [],
                [
                    'speaker_photo' => ['tmp_name' => $tmpPath, 'name' => 'dummy.jpg', 'size' => \strlen($filedata)],
                ]
            );

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);

        \fclose($tmpFile);
    }
}
