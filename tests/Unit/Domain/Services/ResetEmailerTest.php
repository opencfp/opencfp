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
use OpenCFP\Domain\Services\ResetEmailer;
use Swift_Mailer;
use Swift_Message;
use Twig_Template;

/**
 * @covers \OpenCFP\Domain\Services\ResetEmailer
 */
final class ResetEmailerTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function it_sends_the_expected_email()
    {
        $userEmail = 'user@example.com';

        /* @var Swift_Mailer $swiftMailer */
        $swiftMailer = Mockery::mock(Swift_Mailer::class)
            ->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (Swift_Message $message) use ($userEmail) {
                return $message->getTo() === [
                    $userEmail => null,
                ];
            }))
            ->getMock();

        /* @var Twig_Template $template */
        $template = Mockery::mock(Twig_Template::class)->shouldIgnoreMissing();

        $twig = Mockery::mock(\Twig_Environment::class);
        $twig->shouldReceive('loadTemplate')
            ->withArgs(['emails/reset_password.twig'])
            ->andReturn($template);
        $options = [
            'host'     => 'localhost',
            'port'     => 25,
            'username' => 'test',
            'password' => 'test',
        ];
        $resetEmailer = new ResetEmailer(
            $swiftMailer,
            $twig,
            'admin@example.com',
            'Reset'
        );

        $response = $resetEmailer->send(
            123,
            $userEmail,
            '987abc'
        );

        $this->assertNotFalse($response);
    }
}
