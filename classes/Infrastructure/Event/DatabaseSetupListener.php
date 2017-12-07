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

namespace OpenCFP\Infrastructure\Event;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DatabaseSetupListener implements EventSubscriberInterface
{
    /**
     * @var Capsule
     */
    private $capsule;

    public function __construct(Capsule $capsule)
    {
        $this->capsule = $capsule;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['setup', 512],
            ConsoleEvents::COMMAND => ['setup', 512],
        ];
    }

    public function setup()
    {
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }
}
