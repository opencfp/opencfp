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
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\YesNoRating
 */
class YesNoRatingTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider ratingProvider
     */
    public function testValidRatings($rating, $valid)
    {
        $mockAuth = Mockery::mock(Authentication::class)->shouldIgnoreMissing();
        $metaMock = Mockery::mock(TalkMeta::class);

        $yesno    = new YesNoRating($metaMock, $mockAuth);

        $this->assertSame($valid, $yesno->isValidRating($rating));
    }

    public function testGetRatingNameReturnsYesNo()
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('userId');
        $metaMock = Mockery::mock(TalkMeta::class);

        $yesno    = new YesNoRating($metaMock, $mockAuth);

        $this->assertSame('YesNo', $yesno->getRatingName());
    }

    public function ratingProvider(): array
    {
        return [
            [-1, true],
            [0, true],
            [1, true],
            [2, false],
            [-2, false],
            [10, false],
            [PHP_INT_MAX, false],
            [-0, true],
        ];
    }
}
