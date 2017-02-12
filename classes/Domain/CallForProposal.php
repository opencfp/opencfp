<?php

namespace OpenCFP\Domain;

use DateTimeInterface;
use DateTimeImmutable;
use DateInterval;

/**
 * This object is responsible for representing behaviour around the call
 * for proposal process. Today it is only responsible for reporting whether or not the
 * CFP is open. This is useful in service-layer testing as you can easily modify the temporal
 * aspect.
 */
class CallForProposal
{
    /**
     * @var DateTimeInterface
     */
    private $endDate;

    public function __construct(DateTimeInterface $end)
    {
        if ($end->format('H:i:s') === '00:00:00') {
            $end = $end->add(new DateInterval('PT23H59M59S'));
        }
        $this->endDate = $end;
    }

    /**
     * @return boolean true if CFP is open, false otherwise.
     */
    public function isOpen()
    {
        $now = new DateTimeImmutable('now');

        return $now < $this->endDate;
    }
}
