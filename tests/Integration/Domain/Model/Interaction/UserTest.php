<?php

namespace OpenCFP\Test\Integration\Domain\Model\Interaction;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\DataBaseInteraction;

class UserTest extends BaseTestCase
{
    use DataBaseInteraction;

    /**
     * @test
     */
    public function deleteStillWorksNormally()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->assertCount(1, User::all());

        $this->assertTrue($user->delete());
        $this->assertCount(0, User::all());
    }

    /**
     * @test
     */
    public function deleteDeletesTalksAsWellAsUser()
    {
        $talk = factory(Talk::class, 1)->create()->first();

        $user = $talk->speaker;
        $user->delete();

        $this->assertCount(0, Talk::all());
        $this->assertCount(0, User::all());
    }
}
