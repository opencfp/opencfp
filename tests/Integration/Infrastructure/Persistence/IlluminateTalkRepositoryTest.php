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

namespace OpenCFP\Test\Integration\Infrastructure\Persistence;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @coversNothing
 */
class IlluminateTalkRepositoryTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function RepoIsInstanceOfTalkRepository()
    {
        $repo = new IlluminateTalkRepository();

        $this->assertInstanceOf(TalkRepository::class, $repo);
    }

    /**
     * @test
     */
    public function persistSavesModelIntoDatabase()
    {
        $talk = factory(Talk::class, 1)->make()->first();
        $repo = new IlluminateTalkRepository();

        $this->assertCount(0, Talk::all());
        $repo->persist($talk);
        $this->assertCount(1, Talk::all());
    }
}
