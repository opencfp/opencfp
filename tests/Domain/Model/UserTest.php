<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * @group db
 */
class UserTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function scopeSearchWillReturnAllWhenNoSearch()
    {
        factory(User::class, 3)->create();

        $this->assertCount(3, User::search()->get());
    }

    /**
     * @test
     */
    public function scopeSearchWorksWithNames()
    {
        $this->makeKnownUsers();
        $this->assertCount(5, User::search()->get());
        $this->assertCount(3, User::search('Vries')->get());
        $this->assertCount(1, User::search('Hunter')->get());
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

    /**
     * @test
     */
    public function deleteStillWorksNormally()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->assertTrue($user->delete());
        $this->assertCount(0, User::all());
    }

    private function makeKnownUsers()
    {
        $userInfo = [
            'password' => password_hash('secret', PASSWORD_BCRYPT),
            'activated' => 1,
            'has_made_profile' => 1,
        ];

        User::create(array_merge([
            'email' => 'henk@example.com',
            'first_name' => 'Henk',
            'last_name' => 'de Vries',
        ], $userInfo));

        User::create(array_merge([
            'email' => 'speaker@cfp.org',
            'first_name' => 'Speaker',
            'last_name' => 'de Vries',
        ], $userInfo));

        User::create(array_merge([
            'email' => 'Vries@cfp.org',
            'first_name' => 'Vries',
            'last_name' => 'van Henk',
        ], $userInfo));

        User::create(array_merge([
            'email' => 'd20@mail.com',
            'first_name' => 'Arthur',
            'last_name' => 'Hunter',
        ], $userInfo));

        User::create(array_merge([
            'email' => 'hunter@hunter.xx',
            'first_name' => 'Gon',
            'last_name' => 'Freecss',
        ], $userInfo));
    }
}
