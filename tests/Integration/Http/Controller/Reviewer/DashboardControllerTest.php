<?php

namespace OpenCFP\Test\Integration\Http\Controller\Reviewer;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * @coversNothing
 */
class DashboardControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $talks;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$talks = factory(Talk::class, 2)->create();
    }

    /**
     * @test
     */
    public function indexDisplaysListOfTalks()
    {
        $this->asAdmin()
            ->get('/admin/')
            ->assertSee(self::$talks->first()->title)
            ->assertSuccessful()
            ->assertNoFlashSet();
    }
}
