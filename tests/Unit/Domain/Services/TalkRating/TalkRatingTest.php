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

namespace OpenCFP\Test\Unit\Domain\Services\TalkRating;

use Mockery;
use OpenCFP\Domain\Model\TalkMeta;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;
use OpenCFP\Infrastructure\Auth\UserInterface;

/**
 * We Use the YesNoRating class to test the base class, since we know exactly what values are allowed
 */
final class TalkRatingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function rateThrowsExceptionOnInvalidRating()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(1);

        $mockAuth = Mockery::mock(Authentication::class);
        $metaMock = Mockery::mock(TalkMeta::class);
        $mockAuth->shouldReceive('user')->andReturn($user);

        $sut = new YesNoRating($metaMock, $mockAuth);

        $this->expectException(TalkRatingException::class);
        $this->expectExceptionMessage('Invalid talk rating: 9001');

        $sut->rate(7, 9001);
    }

    /**
     * @test
     */
    public function rate()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(1);

        $mockAuth = Mockery::mock(Authentication::class);
        $mockAuth->shouldReceive('user')->andReturn($user);

        $metaMock = Mockery::mock(TalkMeta::class)->makePartial();
        $metaMock->shouldReceive('firstOrCreate')->andReturnSelf();
        $metaMock->shouldReceive('save');

        $sut = new YesNoRating($metaMock, $mockAuth);

        $sut->rate(7, 1);

        $this->assertSame(1, $metaMock->rating);
    }
}
