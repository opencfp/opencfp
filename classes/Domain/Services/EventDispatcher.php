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

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

/**
 * We're extending the Symfony dispatcher to bring the abstraction into
 * the OpenCFP namespace in case we ever want to use another implementation.
 * Don't go adding a bunch of stuff here. If we ever decide to put any more
 * methods on this interface... just make an interface and fix implementation.
 */
class EventDispatcher extends SymfonyEventDispatcher
{
}
