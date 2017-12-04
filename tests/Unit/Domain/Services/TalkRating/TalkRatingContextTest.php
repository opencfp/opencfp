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
use OpenCFP\Domain\Services\TalkRating\OneToTenRating;
use OpenCFP\Domain\Services\TalkRating\TalkRating;
use OpenCFP\Domain\Services\TalkRating\TalkRatingContext;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\TalkRatingContext
 */
class TalkRatingContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider strategyProvider
     */
    public function testGetTalkRatingStrategyReturnsCorrectInstance(string $input, string $expectedClassName)
    {
        $strategy = TalkRatingContext::getTalkStrategy($input, $this->authMock());
        $this->assertInstanceOf($expectedClassName, $strategy);
    }

    public function strategyProvider(): array
    {
        return [
            'Input is empty string' => [
                '',
                YesNoRating::class,
            ],
            'Input is yesno' => [
                'yesno',
                YesNoRating::class,
            ],
            'Input is onetoten' => [
                'onetoten',
                OneToTenRating::class,
            ],
            'Casing of yesno doesnt matter' => [
                'YeSNo',
                YesNoRating::class,
            ],
            'Casing of onetoten doesnt matter' => [
                'OnEToTen',
                OneToTenRating::class,
            ],
            'Giberish defaults to YesNo' => [
                'asdfgo87yhl',
                YesNoRating::class,
            ],
        ];
    }

    public function testGetTalkStrategyReturnsATalkRatingStrategy()
    {
        $strategy = TalkRatingContext::getTalkStrategy('yesno', $this->authMock());
        $this->assertInstanceOf(TalkRatingStrategy::class, $strategy);
    }

    public function testGetTalkRatingStrategyIsATalkRating()
    {
        $strategy = TalkRatingContext::getTalkStrategy('yesno', $this->authMock());
        $this->assertInstanceOf(TalkRating::class, $strategy);
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
