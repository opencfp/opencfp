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
final class OneToTenRatingTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider validRatingProvider
     */
    public function testGoodRatingsAreSuccessful(int $rating)
    {
        $mockAuth    = Mockery::mock(Authentication::class)->shouldIgnoreMissing();
        $metaMock    = Mockery::mock(TalkMeta::class);
        $oneToTen    = new OneToTenRating($metaMock, $mockAuth);
        $this->assertTrue($oneToTen->isValidRating($rating));
    }

    /**
     * @dataProvider invalidRatingProvider
     */
    public function testBadRatingsAreNotSuccessFul(int $rating)
    {
        $mockAuth    = Mockery::mock(Authentication::class)->shouldIgnoreMissing();
        $metaMock    = Mockery::mock(TalkMeta::class);
        $oneToTen    = new OneToTenRating($metaMock, $mockAuth);
        $this->assertFalse($oneToTen->isValidRating($rating));
    }

    public function testGetRatingNameReturnsOneToTen()
    {
        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('userId');
        $metaMock    = Mockery::mock(TalkMeta::class);
        $oneToTen    = new OneToTenRating($metaMock, $mockAuth);
        $this->assertSame('OneToTen', $oneToTen->getRatingName());
    }

    public function validRatingProvider(): array
    {
        return [
            [0],
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
            [9],
            [10],
        ];
    }

    public function invalidRatingProvider(): array
    {
        return [
            [-1],
            [11],
            [PHP_INT_MAX],
            [100],
            [12],
            [-5],
        ];
    }
}
