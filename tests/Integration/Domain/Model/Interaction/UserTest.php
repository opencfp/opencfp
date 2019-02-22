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

namespace OpenCFP\Test\Integration\Domain\Model\Interaction;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class UserTest extends WebTestCase implements TransactionalTestCase
{
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
