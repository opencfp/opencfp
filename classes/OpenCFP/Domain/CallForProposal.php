<?php

namespace OpenCFP\Domain;

use DateTime;

class CallForProposal
{
    /**
     * @var DateTime
     */
    private $endDate;

    public function __construct(DateTime $end){
        $this->endDate = $end;
    }

    /**
     * @return boolean true if CFP is open, false otherwise.
     */
    public function isOpen()
    {
        $now = new DateTime('now');

        return $now < $this->endDate;
    }
} 