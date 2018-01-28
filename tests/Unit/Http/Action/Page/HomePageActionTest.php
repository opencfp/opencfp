<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Http\Action\Page;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Http\Action\Page\HomePageAction;
use PHPUnit\Framework\TestCase;

final class HomePageActionTest extends TestCase
{
    use Helper;

    public function testItReturnsTheCorrectContentIfNoSubmissionCountNeedsToBeShown()
    {
        $talk   = $this->prophesize(Talk::class);
        $action = new HomePageAction(false, $talk->reveal());

        $expected = [
            'number_of_talks' => '',
        ];

        $this->assertSame($expected, $action());
    }

    public function testItReturnsTheCorrectAmountOfTalksIfRequired()
    {
        $faker     = $this->faker();
        $talkCount = $faker->numberBetween(1);

        $talk = $this->prophesize(Talk::class);

        $talk->count()
            ->shouldBeCalled()
            ->willReturn($talkCount);

        $action = new HomePageAction(true, $talk->reveal());

        $expected = [
            'number_of_talks' => $talkCount,
        ];

        $this->assertSame($expected, $action());
    }
}
