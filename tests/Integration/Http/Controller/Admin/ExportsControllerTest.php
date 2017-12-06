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

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

/**
 * @covers \OpenCFP\Http\Controller\Admin\ExportsController
 * @covers \OpenCFP\Http\Controller\BaseController
 */
final class ExportsControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $talks;
    private static $selectedTalk;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$talks        = factory(Talk::class, 5)->create();
        self::$selectedTalk = factory(Talk::class, 1)->create(['selected' => 1, 'slides' => '=2+3'])->first();
    }

    /**
     * @test
     */
    public function anonymousTalksExportsContainsNoUserNames()
    {
        $user = self::$talks->first()->speaker()->first();

        $response = $this
            ->asAdmin()
            ->get('/admin/export/csv/anon');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$talks->first()->title, $response);
        $this->assertResponseBodyNotContains($user->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function attributedTalksWorks()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/export/csv');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$talks->first()->title, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function emailExportWorks()
    {
        $user = self::$talks->first()->speaker()->first();

        $response = $this
            ->asAdmin()
            ->get('/admin/export/csv/emails');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains(self::$talks->first()->title, $response);
        $this->assertResponseBodyContains($user->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * @test
     */
    public function selectedExportWorks()
    {
        $user = self::$talks->first()->speaker()->first();

        $response = $this
            ->asAdmin()
            ->get('/admin/export/csv/selected');

        $this->assertResponseBodyContains(self::$selectedTalk->title, $response);
        $this->assertResponseBodyNotContains(self::$talks->first()->title, $response);
        $this->assertResponseBodyNotContains($user->first_name, $response);
        $this->assertSessionHasNoFlashMessage($this->container->get('session'));
    }

    /**
     * This test assures that entries with that start with a formula character get prepended with a '
     *
     * @test
     */
    public function talksGetProperlyFormatted()
    {
        $response = $this
            ->asAdmin()
            ->get('/admin/export/csv/selected');

        $this->assertResponseBodyContains(",'=2+3", $response);
        $this->assertResponseBodyNotContains(',=2+3', $response);
    }
}
