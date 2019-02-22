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

namespace OpenCFP\Test\Unit\Domain\Talk;

use Illuminate\Database\Eloquent;
use Illuminate\Support\Collection;
use Localheinz\Test\Util\Helper;
use Mockery as m;
use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Domain\Talk\TalkProfile;

final class TalkProfileTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function getSpeakerReturnsSpeakerProfile()
    {
        $user          = m::mock(User::class);
        $talk          = m::mock(Talk::class)->makePartial()->makePartial();
        $talk->speaker = $user;
        $talkProfile   = new TalkProfile($talk);
        $speaker       = $talkProfile->getSpeaker();
        $this->assertInstanceOf(SpeakerProfile::class, $speaker);
    }

    /**
     * @test
     */
    public function getIdReturnsId()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->id    = 2;
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->id, $talkProfile->getId());
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->title = 'Title';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->title, $talkProfile->getTitle());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsDescription()
    {
        $talk              = m::mock(Talk::class)->makePartial();
        $talk->description = 'Describe the talk';
        $talkProfile       = new TalkProfile($talk);
        $this->assertSame($talk->description, $talkProfile->getDescription());
    }

    /**
     * @test
     */
    public function getOtherReturnsOther()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->other = 'Other information';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->other, $talkProfile->getOther());
    }

    /**
     * @test
     */
    public function getTypeReturnsType()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->type  = 'api';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->type, $talkProfile->getType());
    }

    /**
     * @test
     */
    public function getLevelReturnsLevel()
    {
        $talk        = m::mock(Talk::class)->makePartial();
        $talk->level = 'starter';
        $talkProfile = new TalkProfile($talk);
        $this->assertSame($talk->level, $talkProfile->getLevel());
    }

    /**
     * @test
     */
    public function getCategoryReturnsCategory()
    {
        $talk           = m::mock(Talk::class)->makePartial();
        $talk->category = 'blep';
        $talkProfile    = new TalkProfile($talk);
        $this->assertSame($talk->category, $talkProfile->getCategory());
    }

    /**
     * @test
     */
    public function getSlidesReturnsSlides()
    {
        $talk         = m::mock(Talk::class)->makePartial();
        $talk->slides = 'http://www.example.com/slides.png';
        $talkProfile  = new TalkProfile($talk);
        $this->assertSame($talk->slides, $talkProfile->getSlides());
    }

    /**
     * @test
     */
    public function isDesiredReturnsBool()
    {
        $talk          = m::mock(Talk::class)->makePartial();
        $talk->desired = 0;
        $talkProfile   = new TalkProfile($talk);
        $isDesired     = $talkProfile->isDesired();
        $this->assertFalse($isDesired);
    }

    /**
     * @test
     */
    public function isSponsorReturnsBool()
    {
        $talk          = m::mock(Talk::class)->makePartial();
        $talk->sponsor = 1;
        $talkProfile   = new TalkProfile($talk);
        $isSponsor     = $talkProfile->isSponsor();
        $this->assertTrue($isSponsor);
    }

    /**
     * @test
     */
    public function isSpeakerFavoriteReturnsBool()
    {
        $talk              = m::mock(Talk::class)->makePartial();
        $talk->favorite    = 1;
        $talkProfile       = new TalkProfile($talk);
        $isSpeakerFavorite = $talkProfile->isSpeakerFavorite();
        $this->assertTrue($isSpeakerFavorite);
    }

    /**
     * @test
     */
    public function isSelectedReturnsBool()
    {
        $talk           = m::mock(Talk::class)->makePartial();
        $talk->selected = 0;
        $talkProfile    = new TalkProfile($talk);
        $isSelected     = $talkProfile->isSelected();
        $this->assertFalse($isSelected);
    }

    /**
     * @test
     */
    public function getCommentsReturnsComments()
    {
        $many = m::mock(Eloquent\Relations\HasMany::class);
        $many->shouldReceive('get')->andReturn(collect());
        $talk = m::mock(Talk::class)->makePartial();
        $talk->shouldReceive('comments')->andReturn($many);
        $talkProfile = new TalkProfile($talk);
        $comments    = $talkProfile->getComments();
        $this->assertInstanceOf(Collection::class, $comments);
        //The talk has no comments so it returns 0.
        $this->assertCount(0, $comments);
    }

    /**
     * @test
     */
    public function getRatingReturnsZeroWhenTalkHasNoMeta()
    {
        $userId = $this->faker()->numberBetween(1);

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andThrow(new Eloquent\ModelNotFoundException());

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertSame(0, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function getRatingReturnsZeroWhenTalkHasMetaButNoRating()
    {
        $userId = $this->faker()->numberBetween(1);

        $talkMeta = m::mock(TalkMeta::class);

        $talkMeta
            ->shouldReceive('getAttribute')
            ->with('rating')
            ->andReturn(null);

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andReturn($talkMeta);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertSame(0, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function getRatingReturnsRatingCastedToIntWhenTalkHasMeta()
    {
        $faker = $this->faker();

        $userId = $faker->numberBetween(1);
        $rating = $this->faker()->numberBetween(1);

        $talkMeta = m::mock(TalkMeta::class);

        $talkMeta
            ->shouldReceive('getAttribute')
            ->with('rating')
            ->andReturn((string) $rating);

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andReturn($talkMeta);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertSame($rating, $talkProfile->getRating());
    }

    /**
     * @test
     */
    public function isViewedByMeReturnsFalseWhenTalkHasNoMeta()
    {
        $userId = $this->faker()->numberBetween(1);

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andThrow(new Eloquent\ModelNotFoundException());

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertFalse($talkProfile->isViewedByMe());
    }

    /**
     * @test
     */
    public function isViewedByMeReturnsFalseWhenTalkHasMetaAndViewedIsZero()
    {
        $userId = $this->faker()->numberBetween(1);

        $talkMeta = m::mock(TalkMeta::class);

        $talkMeta
            ->shouldReceive('getAttribute')
            ->with('viewed')
            ->andReturn('0');

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andReturn($talkMeta);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertFalse($talkProfile->isViewedByMe());
    }

    /**
     * @test
     */
    public function isViewedByMeReturnsTrueWhenTalkHasMetaAndViewedIsOne()
    {
        $userId = $this->faker()->numberBetween(1);

        $talkMeta = m::mock(TalkMeta::class);

        $talkMeta
            ->shouldReceive('getAttribute')
            ->with('viewed')
            ->andReturn('1');

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('getMetaFor')
            ->with($userId)
            ->andReturn($talkMeta);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertTrue($talkProfile->isViewedByMe());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsFalseWhenFavoritesAreEmpty()
    {
        $userId = $this->faker()->numberBetween(1);

        $relation = m::mock(Eloquent\Relations\HasMany::class);

        $relation
            ->shouldReceive('get')
            ->andReturn(new Collection([]));

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('favorites')
            ->andReturn($relation);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertFalse($talkProfile->isMyFavorite());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsFalseWhenThereIsNoFavoriteWhereAdminUserIdEqualsUserId()
    {
        $faker = $this->faker();

        $userId      = $faker->unique()->numberBetween(1);
        $adminUserId = $faker->unique()->numberBetween(1);

        $favorite = m::mock(Favorite::class);

        $favorite
            ->shouldReceive('getAttribute')
            ->with('admin_user_id')
            ->andReturn((string) $adminUserId);

        $relation = m::mock(Eloquent\Relations\HasMany::class);

        $relation
            ->shouldReceive('get')
            ->andReturn(new Collection([
                $favorite,
            ]));

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('favorites')
            ->andReturn($relation);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertFalse($talkProfile->isMyFavorite());
    }

    /**
     * @test
     */
    public function isMyFavoriteReturnsTrueWhenThereIsAFavoriteWhereAdminUserIdEqualsUserId()
    {
        $faker = $this->faker();

        $userId      = $faker->unique()->numberBetween(1);
        $adminUserId = $faker->unique()->numberBetween(1);

        $favoriteOne = m::mock(Favorite::class);

        $favoriteOne
            ->shouldReceive('getAttribute')
            ->with('admin_user_id')
            ->andReturn((string) $adminUserId);

        $favoriteTwo = m::mock(Favorite::class);

        $favoriteTwo
            ->shouldReceive('getAttribute')
            ->with('admin_user_id')
            ->andReturn((string) $userId);

        $relation = m::mock(Eloquent\Relations\HasMany::class);

        $relation
            ->shouldReceive('get')
            ->andReturn(new Collection([
                $favoriteOne,
                $favoriteTwo,
            ]));

        $talk = m::mock(Talk::class);

        $talk
            ->shouldReceive('favorites')
            ->andReturn($relation);

        $talkProfile = new TalkProfile(
            $talk,
            $userId
        );

        $this->assertTrue($talkProfile->isMyFavorite());
    }
}
