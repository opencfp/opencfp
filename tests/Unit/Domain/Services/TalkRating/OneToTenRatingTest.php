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
use OpenCFP\Domain\Services\TalkRating\OneToTenRating;

/**
 * @covers \OpenCFP\Domain\Services\TalkRating\OneToTenRating
 */
class OneToTenRatingTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider ratingProvider
     */
    public function testValidRatings($rating, $valid)
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('userId');
        $metaMock    = Mockery::mock(TalkMeta::class);
        $oneToTen    = new OneToTenRating($metaMock, $mockAuth);
        $this->assertSame($valid, $oneToTen->isValidRating($rating));
    }

    public function testGetRatingNameReturnsOneToTen()
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('userId');
        $metaMock    = Mockery::mock(TalkMeta::class);
        $oneToTen    = new OneToTenRating($metaMock, $mockAuth);
        $this->assertSame('OneToTen', $oneToTen->getRatingName());
    }

    public function ratingProvider()
    {
        return [
            [-1, false],
            [0, true],
            [1, true],
            [5, true],
            [10, true],
            [11, false],
            [9, true],
            [PHP_INT_MAX, false],
            [-0, true],
        ];
    }
}
