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

namespace OpenCFP\Test\Integration\Domain\Services;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class TalkFormatterTest extends WebTestCase implements TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->generateSomeTalks();
    }

    /**
     * @test
     */
    public function formatListReturnsAllTalksAsCollection()
    {
        $formatter = new TalkFormatter();
        $talks     = Talk::all();
        $formatted = $formatter->formatList($talks, 2);
        $this->assertSame(\count($talks), \count($formatted));
        $this->assertInstanceOf(Collection::class, $formatted);
    }

    private function generateSomeTalks()
    {
        $talk = new Talk();

        $talk->create(
            [
                'user_id'     => 1,
                'title'       => 'One talk to rule them all',
                'description' => 'Two is fine too',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'api',
            ]
        );

        $meta = new TalkMeta();
        $meta->create(
            [
                'admin_user_id' => 2,
                'rating'        => 1,
                'viewed'        => 1,
                'talk_id'       => $talk->first()->id,
                'created'       => new \DateTime(),
            ]
        );
        $talk->create(
            [
                'user_id'     => 8,
                'title'       => 'Extra Extra',
                'description' => 'Talk',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'api',
            ]
        );
        $talk->create(
            [
                'user_id'     => 8,
                'title'       => 'Third',
                'description' => 'Talk',
                'type'        => 'regular',
                'level'       => 'entry',
                'category'    => 'api',
            ]
        );
    }
}
