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

namespace OpenCFP\Test\Integration\Http\Controller\Admin;

use Illuminate\Database\Eloquent;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ExportsControllerTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function anonymousTalksExportsContainsNoUserNames()
    {
        /** @var User $admin */
        $admin = factory(User::class)->create()->first();

        /** @var Eloquent\Collection|Talk[] $talks */
        $talks = factory(Talk::class, 3)->create();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/export/csv/anon');

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasNoFlashMessage($this->session());

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
            $this->assertResponseBodyNotContains($talk->speaker()->first()->first_name, $response);
        }
    }

    /**
     * @test
     */
    public function attributedTalksWorks()
    {
        /** @var User $admin */
        $admin = factory(User::class)->create()->first();

        /** @var Eloquent\Collection|Talk[] $talks */
        $talks = factory(Talk::class, 2)->create();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/export/csv');

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasNoFlashMessage($this->session());

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }
    }

    /**
     * @test
     */
    public function emailExportWorks()
    {
        /** @var User $admin */
        $admin = factory(User::class)->create()->first();

        /** @var Eloquent\Collection|Talk[] $talks */
        $talks = factory(Talk::class, 2)->create();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/export/csv/emails');

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasNoFlashMessage($this->session());

        foreach ($talks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }
    }

    /**
     * @test
     */
    public function selectedExportWorks()
    {
        /** @var User $admin */
        $admin = factory(User::class)->create()->first();

        /** @var Eloquent\Collection|Talk[] $talks */
        $talks = factory(Talk::class, 2)->create(['selected' => 0]);

        /** @var Eloquent\Collection|Talk[] $selectedTalks */
        $selectedTalks = factory(Talk::class, 2)->create(['selected' => 1]);

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/export/csv/selected');

        $this->assertResponseIsSuccessful($response);
        $this->assertSessionHasNoFlashMessage($this->session());

        foreach ($talks as $talk) {
            $this->assertResponseBodyNotContains($talk->title, $response);
        }

        foreach ($selectedTalks as $talk) {
            $this->assertResponseBodyContains($talk->title, $response);
        }
    }

    /**
     * This test assures that entries with that start with a formula character get prepended with a '
     *
     * @test
     */
    public function talksGetProperlyFormatted()
    {
        /** @var User $admin */
        $admin = factory(User::class)->create()->first();

        factory(Talk::class, 1)->create(['selected' => 1, 'slides' => '=2+3'])->first();

        $response = $this
            ->asAdmin($admin->id)
            ->get('/admin/export/csv/selected');

        $this->assertResponseBodyContains(",'=2+3", $response);
        $this->assertResponseBodyNotContains(',=2+3', $response);
    }
}
