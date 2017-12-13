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

namespace OpenCFP\Test\Unit\Domain\Services;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\TalkEmailer;
use Swift_Mailer;
use Swift_Message;

/**
 * @covers \OpenCFP\Domain\Services\TalkEmailer
 */
final class TalkEmailerTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function it_sends_the_expected_email()
    {
        /* @var Swift_Mailer $swiftMailer */
        $swiftMailer = Mockery::mock(Swift_Mailer::class);
        $swiftMailer->shouldReceive('send')->andReturn(1);

        // Create a message
        $talkEmailer = new TalkEmailer($swiftMailer);
        $response    = $talkEmailer->send(new Swift_Message());

        $this->assertNotFalse($response);
    }
}
