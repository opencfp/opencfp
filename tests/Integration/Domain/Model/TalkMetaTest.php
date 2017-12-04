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

namespace OpenCFP\Test\Integration\Domain\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @coversNothing
 */
final class TalkMetaTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @var TalkMeta
     */
    private static $meta;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$meta = factory(TalkMeta::class, 1)->create()->first();
    }

    /**
     * @test
     */
    public function talkRelationWorks()
    {
        $talk = self::$meta->talk();
        $this->assertInstanceOf(BelongsTo::class, $talk);
        $this->assertInstanceOf(Talk::class, $talk->first());
    }

    /**
     * @test
     */
    public function userRelationWorks()
    {
        $user = self::$meta->user();
        $this->assertInstanceOf(BelongsTo::class, $user);
        $this->assertInstanceOf(User::class, $user->first());
    }
}
