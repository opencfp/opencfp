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

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkTest extends WebTestCase implements TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpTalksForTests();
    }

    /**
     * @test
     */
    public function recentReturnsLatestTalksFirst()
    {
        $recent = Talk::recent()->get();
        $this->assertSame('talks title NO 3', $recent->first()->title);
        $this->assertSame('talks title', $recent->last()->title);
    }

    /** @test */
    public function recentReturnsAnArrayOfTalks()
    {
        $this->assertCount(3, Talk::recent()->get());
        $this->assertCount(2, Talk::recent(2)->get());
    }

    /**
     * @test
     */
    public function selectedOnlyReturnsSelectedTalks()
    {
        $selected = Talk::selected()->get();
        $this->assertCount(1, $selected);
        $this->assertSame('talks title NO 2', $selected->first()->title);
    }

    /**
     * @test
     */
    public function viewedByOnlyReturnsViewedTalks()
    {
        $viewedBy = Talk::viewedBy(1)->get();
        $this->assertCount(2, $viewedBy);

        $viewedByOther = Talk::viewedBy(25)->get();
        $this->assertCount(0, $viewedByOther);
    }

    /**
     * @test
     */
    public function favoritedByOnlyReturnsFavoritedTalks()
    {
        $favorited = Talk::favoritedBy(1)->get();

        $this->assertCount(1, $favorited);
        $this->assertSame('talks title', $favorited->first()->title);

        $favoritedByOther = Talk::viewedBy(25)->get();
        $this->assertCount(0, $favoritedByOther);
    }

    /**
     * @test
     */
    public function ratedPlusOneByReturnsPlusOneRatedTalks()
    {
        $ratedPlusOne = Talk::ratedPlusOneBy(1)->get();

        $this->assertCount(1, $ratedPlusOne);
        $this->assertSame('talks title NO 2', $ratedPlusOne->first()->title);

        $ratedPlusOneByOther = Talk::ratedPlusOneBy(25)->get();
        $this->assertCount(0, $ratedPlusOneByOther);
    }

    /**
     * @test
     */
    public function notRatedByReturnsTalksNotRatedOrRatedZero()
    {
        $notRated = Talk::notRatedBy(1)->get();
        $this->assertCount(2, $notRated);
        $this->assertTrue($notRated->contains(function ($value) {
            return $value->title == 'talks title';
        }));
        $this->assertTrue($notRated->contains(function ($value) {
            return $value->title == 'talks title NO 3';
        }));

        $notRatedByUserTwo = Talk::notRatedBy(25)->get();
        $this->assertCount(3, $notRatedByUserTwo);
    }

    /**
     * @test
     */
    public function notViewedByReturnsTalksNotViewed()
    {
        $notViewed = Talk::notViewedBy(1)->get();

        $this->assertCount(1, $notViewed);
        $this->assertSame('talks title NO 3', $notViewed->first()->title);

        $notViewedByOther = Talk::notViewedBy(2)->get();

        $this->assertCount(3, $notViewedByOther);
    }

    /**
     * @test
     */
    public function topRatedSortsOnBestRatings()
    {
        $topRated = Talk::topRated()->get();
        $this->assertSame('talks title NO 2', $topRated->first()->title);
        $this->assertCount(2, $topRated);
    }

    private function setUpTalksForTests()
    {
        $talk = Talk::create([
            'user_id'     => 7,
            'title'       => 'talks title',
            'description' => 'Long description',
            'type'        => '',
            'level'       => 'entry',
            'category'    => 'api',
            'selected'    => 0,
            'created_at'  => '2018-01-01 00:00:00',
        ]);

        $talkTwo = Talk::create([
            'user_id'     => 7,
            'title'       => 'talks title NO 2',
            'description' => 'Long description',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
            'selected'    => 1,
            'created_at'  => '2018-01-01 00:00:05',
        ]);

        Talk::create([
            'user_id'     => 7,
            'title'       => 'talks title NO 3',
            'description' => 'Long description',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
            'selected'    => 0,
            'created_at'  => '2018-01-01 00:00:10',
        ]);

        TalkMeta::create([
            'admin_user_id' => 1,
            'talk_id'       => $talk->id,
            'rating'        => 0,
            'viewed'        => 1,
        ]);

        TalkMeta::create([
            'admin_user_id' => 1,
            'talk_id'       => $talkTwo->id,
            'rating'        => 1,
            'viewed'        => 1,
        ]);
        TalkMeta::create([
            'admin_user_id' => 2,
            'talk_id'       => $talkTwo->id,
            'rating'        => 1,
            'viewed'        => 0,
        ]);
        TalkMeta::create([
            'admin_user_id' => 8,
            'talk_id'       => $talk->id,
            'rating'        => 1,
            'viewed'        => 0,
        ]);
        Favorite::create([
            'admin_user_id' => 1,
            'talk_id'       => $talk->id,
        ]);
    }
}
