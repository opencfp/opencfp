<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\DatabaseTestCase;

/**
 * @group db
 */
class TalkTest extends DatabaseTestCase
{

    /** @test */
    public function recentReturnsAnArrayOfTalks()
    {
        factory(Talk::class, 10)->create();

        $this->assertCount(10, Talk::recent()->get());
        $this->assertCount(3, Talk::recent(3)->get());
    }
}
