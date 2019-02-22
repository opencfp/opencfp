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

namespace OpenCFP\Test\Unit\Domain;

use OpenCFP\Domain\CallForPapers;

final class CallForPapersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider stillOpenCfPsProvider
     */
    public function it_should_tell_whether_or_not_the_cfp_is_open($endDate)
    {
        $cfp = new CallForPapers(new \DateTimeImmutable($endDate));
        $this->assertTrue($cfp->isOpen());
    }

    public function stillOpenCfPsProvider(): array
    {
        return [
            ['+1 day'],
            [(new \DateTimeImmutable())->format('d.m.Y')],
        ];
    }

    /** @test */
    public function it_should_say_cfp_is_closed_after_end_date_has_passed()
    {
        $cfp = new CallForPapers(new \DateTimeImmutable('-1 day'));
        $this->assertFalse($cfp->isOpen());
    }
}
