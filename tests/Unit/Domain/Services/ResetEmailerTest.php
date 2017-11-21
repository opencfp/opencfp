<?php

namespace OpenCFP\Test\Unit\Domain\Services;

use Mockery;
use OpenCFP\Domain\Services\ResetEmailer;
use Swift_Mailer;
use Swift_Message;
use Twig_Template;

class ResetEmailerTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

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
            ->getMock()
        ;

        /* @var Twig_Template $template */
        $template = Mockery::mock(Twig_Template::class)->shouldIgnoreMissing();

        $resetEmailer = new ResetEmailer(
            $swiftMailer,
            $template,
            'admin@example.com',
            'Reset'
        );

        $response = $resetEmailer->send(
            123,
            $userEmail,
            '987abc'
        );

        $this->assertNotSame($response, false);
    }
}
