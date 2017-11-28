<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;
use Symfony\Component\EventDispatcher\Event;

class TalkWasSubmitted extends Event
{
    /** @var Talk */
    private $talk;

    public function __construct(Talk $talk)
    {
        $this->talk = $talk;
    }

    public function getTalk()
    {
        return $this->talk;
    }
}
