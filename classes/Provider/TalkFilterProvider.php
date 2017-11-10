<?php

namespace OpenCFP\Provider;

use OpenCFP\Domain\Talk\TalkFilter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TalkFilterProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkFilter::class] = function ($app) {
            return new TalkFilter($app['spot']);
        };
    }
}
