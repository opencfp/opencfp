<?php

namespace OpenCFP\Tests\Domain\Services;

use OpenCFP\Domain\Services\ResetEmailer;

class EmailerTest extends \PHPUnit_Framework_TestCase
{
    private $swift_mailer;
    private $template;
    private $config_email;
    private $config_title;

    private $user_id;
    private $user_email;
    private $reset_code;
    private $reset_mailer;

    public function setUp()
    {
        $this->swift_mailer = \Mockery::mock('Swift_Mailer')->shouldReceive('send')->once()
            ->with(\Mockery::on($this->validateEmail()))->getMock();

        $this->template = \Mockery::mock('Twig_Template')->shouldIgnoreMissing();
        $this->config_email = 'admin@example.com';
        $this->config_title = 'Reset';

        $this->user_email = 'user@example.com';
        $this->user_id = 123;
        $this->reset_code = '987abc';

        $this->reset_mailer = new ResetEmailer($this->swift_mailer, $this->template, $this->config_email, $this->config_title);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function it_sends_the_expected_email()
    {
        $this->reset_mailer->send($this->user_id, $this->user_email, $this->reset_code);
    }

    private function validateEmail()
    {
        return function ($message) {
            return $message->getTo() === [$this->user_email => null];
        };
    }
}
