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

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Persistence;
use OpenCFP\Domain\Model\Reminder;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Model\Throttle;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ProcessDeleteActionTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function cannotDeleteAUserWhoIsNotLoggedIn()
    {
        $response = $this->post('/profile/delete');
        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyContains('Redirecting to /dashboard', $response);
    }

    /**
     * @test
     */
    public function deletedUserHasNoRecords()
    {
        // Create a talk and a user to go with it
        $talk = factory(Talk::class)->create()->first();
        $talk->save();
        $user = User::find($talk->user_id);
        $this->assertNotNull($user);
        $user->save();
        $this->assertNotNull(Talk::find($talk->id));

        // Add a favorite to a talkA
        $favorite = Favorite::create(['admin_user_id' => $talk->user_id, 'talk_id' => $talk->id]);
        $favorite->save();
        $this->assertNotNull($favorite);

        // Add a comment to a talk
        $comment = TalkComment::create([
            'user_id' => $talk->user_id,
            'talk_id' => $talk->id,
            'message' => 'This comment should be deleted',
        ]);
        $comment->save();
        $this->assertNotNull($comment);

        // Add some meta details for the talk
        $meta = TalkMeta::create(['admin_user_id' => $user->id, 'talk_id' => $talk->id]);
        $meta->save();
        $this->assertNotNull($meta);

        // Make sure some values end up in the persistence, reminders, and throttle tables
        $persistence = Persistence::create(['user_id' => $user->id, 'code' => 'opencfp123']);
        $persistence->save();
        $this->assertNotNull(Persistence::find($persistence->id));
        $reminder = Reminder::create(['user_id' => $user->id, 'code' => 'opencfp123']);
        $reminder->save();
        $this->assertNotNull(Reminder::find($reminder->id));
        $throttle = Throttle::create([
            'user_id'         => $user->id,
            'last_attempt_at' => \date('Y-m-d H:i:s'),
            'suspended_at'    => \date('Y-m-d H:i:s'),
            'banned_at'       => \date('Y-m-d H:i:s'),
            'type'            => 'opencfp',
        ]);
        $throttle->save();
        $this->assertNotNull(Throttle::find($throttle->id));

        // Trigger a request by a logged-in user to delete their information
        $response = $this
            ->asLoggedInSpeaker($talk->user_id)
            ->post('/profile/delete');
        $this->assertResponseIsRedirect($response);
        $this->assertResponseBodyContains('Redirecting to /', $response);

        // Verify that all the data associated with this user is gone
        $this->assertNull(User::find($talk->user_id));
        $this->assertNull(Talk::find($talk->id));
        $this->assertCount(0, Favorite::find(['talk_id' => $talk->id]));
        $this->assertCount(0, TalkComment::find(['talk_id' => $talk->id]));
        $this->assertCount(0, TalkMeta::find(['talk_id' => $talk->id]));
        $this->assertCount(0, Persistence::find(['user_id' => $user->id]));
        $this->assertCount(0, Reminder::find(['user_id' => $user->id]));
    }
}
