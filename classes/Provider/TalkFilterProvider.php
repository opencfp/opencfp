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

namespace OpenCFP\Provider;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkFormatter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TalkFilterProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkFilter::class] = function () {
            return new TalkFilter(new TalkFormatter(), new Talk());
        };
    }
}
