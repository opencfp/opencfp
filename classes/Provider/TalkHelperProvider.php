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
