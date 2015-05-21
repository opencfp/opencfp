<?php

namespace OpenCFP\Domain;

use DateTime;

/**
 * This object is responsible for representing behaviour around the call
 * for proposal process. Today it is only responsible for reporting whether or not the
 * CFP is open. This is useful in service-layer testing as you can easily modify the temporal
 * aspect.
 */
class CallForProposal
{
    /**
     * @var DateTime
     */
    private $endDate;

    public function __construct(DateTime $end)
    {
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
