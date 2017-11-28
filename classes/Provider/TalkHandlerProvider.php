<?php

namespace OpenCFP\Provider;

use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TalkHandlerProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkHandler::class] = function ($app) {
            return new TalkHandler($app[Authentication::class], $app[TalkRatingStrategy::class]);
        };
    }
}
