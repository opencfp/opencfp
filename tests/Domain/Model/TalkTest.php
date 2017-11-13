<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\RefreshDatabase;

/**
 * @group db
 */
class TalkTest extends BaseTestCase
{
    use RefreshDatabase;

    private static $talks;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $talks = factory(Talk::class, 2)->create(['selected' => 1]);
        factory(TalkMeta::class, 1)->create(['admin_user_id' => 2, 'talk_id' => $talks->first()->id]);
        self::$talks = $talks;
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
        $this->assertCount(2, $selected);
    }

    /**
     * @test
     */
    public function viewedByOnlyReturnsViewedTalks()
    {
        $viewedBy = Talk::viewedBy(2)->get();
        $this->assertCount(1, $viewedBy);
        $viewedByOther = Talk::viewedBy(5)->get();
        $this->assertCount(0, $viewedByOther);
    }
}
