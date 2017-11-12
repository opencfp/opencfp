<?php

namespace OpenCFP\Provider;

use OpenCFP\Http\View\TalkHelper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TalkHelperProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkHelper::class] =  function ($app) {
            return new TalkHelper(
                $app->config('talk.categories'),
                $app->config('talk.levels'),
                $app->config('talk.types')
            );
        };
    }
}
