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

namespace OpenCFP\Test\Unit\Domain\Services\TalkRating;

use Mockery;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingContext;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\TalkRatingContext
 */
class TalkRatingContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getTalkStrategyReturnsATalkRatingStrategy()
    {
        $strategy = TalkRatingContext::getTalkStrategy('yesno', $this->authMock());
        $this->assertInstanceOf(TalkRatingStrategy::class, $strategy);
    }

    /**
     * @test
     */
    public function getTalkStrategyWithYesNoReturnsYesNoRating()
    {
        $strategy = TalkRatingContext::getTalkStrategy('yesno', $this->authMock());
        $this->assertInstanceOf(YesNoRating::class, $strategy);
    }

    /**
     * @test
     */
    public function casingDoesNotMatterForGetTalkRatingStrategy()
    {
        $strategy = TalkRatingContext::getTalkStrategy('YeSNo', $this->authMock());
        $this->assertInstanceOf(YesNoRating::class, $strategy);
    }

    /**
     * @dataProvider strategyProvider
     *
     * @test
     */
    public function defaultStrategyIsYesNoRating($input)
    {
        $strategy = TalkRatingContext::getTalkStrategy($input, $this->authMock());
        $this->assertInstanceOf(YesNoRating::class, $strategy);
    }

    public function strategyProvider(): array
    {
        return [
            [1],
            ['asdf'],
            [''],
            ['NULL'],
            [false],
            [true],
        ];
    }

    /**
     * @return Authentication|Mockery\MockInterface
     */
    public function authMock()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('userId');

        return $auth;
    }
}
