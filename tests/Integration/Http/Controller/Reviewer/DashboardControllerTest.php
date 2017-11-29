<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

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
        $this->asReviewer()
            ->get('/reviewer/')
            ->assertSee(self::$talks->first()->title)
            ->assertSuccessful()
            ->assertNoFlashSet();
    }
}
