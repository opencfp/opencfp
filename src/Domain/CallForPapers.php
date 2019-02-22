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

namespace OpenCFP\Domain;

/**
 * This object is responsible for representing behaviour around the call
 * for proposal process. Today it is only responsible for reporting whether or not the
 * CFP is open. This is useful in service-layer testing as you can easily modify the temporal
 * aspect.
 */
class CallForPapers
{
    /**
     * @var \DateTimeInterface
     */
    private $endDate;

    public function __construct(\DateTimeImmutable $end)
    {
        $this->setEndDate($end);
    }

    /**
     * @param \DateTimeInterface $currentTime
     *
     * @return bool true if CFP is open, false otherwise
     */
    public function isOpen(\DateTimeInterface $currentTime = null): bool
    {
        if (!$currentTime) {
            $currentTime = new \DateTimeImmutable('now');
        }

        return $currentTime < $this->endDate;
    }

    private function setEndDate(\DateTimeImmutable $end)
    {
        if ($end->format('H:i:s') === '00:00:00') {
            $end = $end->add(new \DateInterval('P1D'));
        }

        $this->endDate = $end;
    }
}
