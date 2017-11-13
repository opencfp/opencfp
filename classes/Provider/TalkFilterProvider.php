<?php

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
