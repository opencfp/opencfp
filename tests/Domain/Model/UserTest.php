<?php

namespace OpenCFP\Test\Domain\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Model\TalkMeta;
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
     * @var User
     */
    private static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$user = self::makeKnownUsers();
    }

    /**
     * @test
     */
    public function talksRelationWorks()
    {
        $talk = self::$user->talks();
        $this->assertInstanceOf(HasMany::class, $talk);
        $this->assertInstanceOf(Talk::class, $talk->first());
    }

    /**
     * @test
     */
    public function commentRelationWorks()
    {
        $comment = self::$user->comments();
        $this->assertInstanceOf(HasMany::class, $comment);
        $this->assertInstanceOf(TalkComment::class, $comment->first());
    }

    /**
     * @test
     */
    public function metaRelationWorks()
    {
        $meta = self::$user->meta();
        $this->assertInstanceOf(HasMany::class, $meta);
        $this->assertInstanceOf(TalkMeta::class, $meta->first());
    }

    /**
     * @test
     */
    public function scopeSearchWillReturnAllWhenNoSearch()
    {
        $this->assertCount(5, User::search()->get());
    }

    /**
     * @test
     */
    public function scopeSearchWorksWithNames()
    {
        $this->assertCount(5, User::search()->get());
        $this->assertCount(3, User::search('Vries')->get());
        $this->assertCount(1, User::search('Hunter')->get());
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsAllTalksByDefault()
    {
        $talks = self::$user->getOtherTalks();

        $this->assertCount(3, $talks);
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsOtherTalksCorrectly()
    {
        $talks = self::$user->getOtherTalks(1);

        $this->assertCount(2, $talks);
    }

    /**
     * @test
     */
    public function getOtherTalksReturnsNothingWhenUserHasNoTalks()
    {
        $user  = User::where('first_name', 'Vries')->get()->first();
        $talks = $user->getOtherTalks();

        $this->assertCount(0, $talks);
    }

    private static function makeKnownUsers()
    {
        $userInfo = [
            'password'         => password_hash('secret', PASSWORD_BCRYPT),
            'activated'        => 1,
            'has_made_profile' => 1,
        ];

        $user = User::create(array_merge([
            'email'      => 'henk@example.com',
            'first_name' => 'Henk',
            'last_name'  => 'de Vries',
        ], $userInfo));
        self::giveUserThreeTalks($user);
        self::giveUserRelations($user);

        User::create(array_merge([
            'email'      => 'speaker@cfp.org',
            'first_name' => 'Speaker',
            'last_name'  => 'de Vries',
        ], $userInfo));

        User::create(array_merge([
            'email'      => 'Vries@cfp.org',
            'first_name' => 'Vries',
            'last_name'  => 'van Henk',
        ], $userInfo));

        User::create(array_merge([
            'email'      => 'd20@mail.com',
            'first_name' => 'Arthur',
            'last_name'  => 'Hunter',
        ], $userInfo));

        User::create(array_merge([
            'email'      => 'hunter@hunter.xx',
            'first_name' => 'Gon',
            'last_name'  => 'Freecss',
        ], $userInfo));

        return $user;
    }

    public static function giveUserThreeTalks(User $user)
    {
        $userId = $user->id;

        Talk::create([
            'user_id'     => $userId,
            'title'       => 'talks title',
            'description' => 'Long description',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',

        ]);

        Talk::create([
            'user_id'     => $userId,
            'title'       => 'talks title NO 2',
            'description' => 'Long description',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
        ]);

        Talk::create([
            'user_id'     => $userId,
            'title'       => 'talks title NO 3',
            'description' => 'Long description',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',

        ]);
    }

    public static function giveUserRelations(User $user)
    {
        $userId = $user->id;

        TalkComment::create([
            'user_id' => $userId,
            'talk_id' => 893,
            'message' => 'Oh hi Mark.',
        ]);
        TalkMeta::create([
            'admin_user_id' => $userId,
            'talk_id'       => 893,
            'rating'        => 1,
            'viewed'        => 0,
        ]);
    }
}
