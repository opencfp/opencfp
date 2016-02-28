<?php

namespace OpenCFP\Test\Domain\Services;

use OpenCFP\Domain\Services\ResetEmailer;
use Swift_Mailer;
use Twig_Template;

class ResetEmailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Swift_Mailer
     */
    private $swift_mailer;

    /**
     * @var Twig_Template
     */
    private $template;

    /**
     * @var string
     */
    private $config_email;

    /**
     * @var string
     */
    private $config_title;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var string
     */
    private $user_email;

    /**
     * @var string
     */
    private $reset_code;

    /**
     * @var ResetEmailer
     */
    private $reset_mailer;

    protected function setUp()
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

    protected function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function it_sends_the_expected_email()
    {
        $this->reset_mailer->send($this->user_id, $this->user_email, $this->reset_code);
    }

    //
    // Helpers
    //

    private function validateEmail()
    {
        return function ($message) {
            return $message->getTo() === [$this->user_email => null];
        };
    }
}
