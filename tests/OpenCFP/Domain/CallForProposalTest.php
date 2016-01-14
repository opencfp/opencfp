<?php

namespace OpenCFP\Test\Domain;

use DateTime;
use OpenCFP\Domain\CallForProposal;

class CallForProposalTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_tell_whether_or_not_the_cfp_is_open()
    {
        $cfp = new CallForProposal(new DateTime('+1 day'));
        $this->assertTrue($cfp->isOpen());
    }

    /** @test */
    public function it_should_say_cfp_is_closed_after_end_date_has_passed()
    {
        $cfp = new CallForProposal(new DateTime('-1 day'));
        $this->assertFalse($cfp->isOpen());
    }
}
