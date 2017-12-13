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

namespace OpenCFP\Domain\Services;

use Swift_Mailer;
use Swift_Message;

class TalkEmailer
{
    /**
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @param Swift_Mailer $swiftMailer
     */
    public function __construct(Swift_Mailer $swiftMailer)
    {
        $this->swiftMailer = $swiftMailer;
    }

    /**
     * @param Swift_Message $message
     *
     * @return int
     */
    public function send(Swift_Message $message)
    {
        try {
            return $this->swiftMailer->send($message);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }
    }
}
