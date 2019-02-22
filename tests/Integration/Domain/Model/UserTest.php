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

namespace OpenCFP\Test\Integration\Domain\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class UserTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function talksRelationWorks()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        factory(Talk::class, 3)->create(['user_id' => $speaker->id]);

        $talks = $speaker->talks();

        $this->assertInstanceOf(HasMany::class, $talks);
        $this->assertInstanceOf(Talk::class, $talks->first());
    }

    /**
     * @test
     */
    public function commentRelationWorks()
    {
        /** @var User $user */
        $user = factory(User::class, 1)->create()->first();

        factory(TalkComment::class, 3)->create(['user_id' => $user->id]);

        $comments = $user->comments();

        $this->assertInstanceOf(HasMany::class, $comments);
        $this->assertInstanceOf(TalkComment::class, $comments->first());
    }

    /**
     * @test
     */
    public function metaRelationWorks()
    {
        /** @var User $user */
        $user = factory(User::class, 1)->create()->first();

        factory(TalkMeta::class, 3)->create(['admin_user_id' => $user->id]);

        $metas = $user->meta();

        $this->assertInstanceOf(HasMany::class, $metas);
        $this->assertInstanceOf(TalkMeta::class, $metas->first());
    }

    /**
     * @test
     */
    public function scopeSearchWillReturnAllWhenNoSearch()
    {
        $count = $this->faker()->numberBetween(3, 5);

        factory(User::class, $count)->create();

        $this->assertCount($count, User::search()->get());
        $this->assertCount($count, User::search('')->get());
        $this->assertCount($count, User::search(null)->get());
    }

    /**
     * @test
     */
    public function scopeSearchWorksWithNames()
    {
        $faker = $this->faker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;

        factory(User::class, 1)->create(['first_name' => $firstName]);
        factory(User::class, 1)->create(['last_name' => $firstName]);
        factory(User::class, 1)->create(['last_name' => $lastName]);

        $this->assertCount(3, User::search()->get());
        $this->assertCount(2, User::search($firstName)->get());
        $this->assertCount(1, User::search($lastName)->get());
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsAllTalksByDefault()
    {
        $count = $this->faker()->numberBetween(3, 5);

        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        factory(Talk::class, $count)->create(['user_id' => $speaker->id]);

        $this->assertCount($count, $speaker->getOtherTalks());
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsOtherTalksCorrectly()
    {
        $count = $this->faker()->numberBetween(3, 5);

        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        /** @var Collection $talks */
        $talks = factory(Talk::class, $count)->create(['user_id' => $speaker->id]);

        $talk = $talks->random(1)->first();

        $this->assertCount($count - 1, $speaker->getOtherTalks($talk->id));
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsNothingWhenUserHasNoTalks()
    {
        /** @var User $speaker */
        $speaker = factory(User::class, 1)->create()->first();

        $this->assertCount(0, $speaker->getOtherTalks());
    }
}
