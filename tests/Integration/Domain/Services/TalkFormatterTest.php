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

namespace OpenCFP\Test\Integration\Domain\Services;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Talk\TalkFormatter;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @group db
 * @coversNothing
 */
class TalkFormatterTest extends BaseTestCase
{
    use RefreshDatabase;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::generateSomeTalks();
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithNoMeta()
    {
        $talk      = new Talk();
        $formatter = new TalkFormatter();

        $format =$formatter->createdFormattedOutput($talk->first(), 1);

        $this->assertSame('One talk to rule them all', $format->getTitle());
        $this->assertSame('api', $format->getCategory());
        $this->assertSame(0, $format->getRating());
        $this->assertSame(0, $format->isViewedByMe());
    }

    /**
     * @test
     */
    public function createFormattedOutputWorksWithMeta()
    {
        $formatter = new TalkFormatter();
        $talk      = new Talk();

        // Now to see if the meta gets put in correctly
        $secondFormat =$formatter->createdFormattedOutput($talk->first(), 2);

        $this->assertSame(1, $secondFormat->getRating());
        $this->assertSame(1, $secondFormat->isViewedByMe());
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

    private static function generateSomeTalks()
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
