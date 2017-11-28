<?php

namespace OpenCFP\Provider;

use OpenCFP\Domain\Services\TalkRating\TalkRatingContext;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TalkRatingProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkRatingStrategy::class] = function ($app) {
            return TalkRatingContext::getTalkStrategy('YesNo', $app[Authentication::class]);
        };
    }
}
