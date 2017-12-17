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

final class TalkHelperProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[TalkHelper::class] = function ($app) {
            $categories = $app->config('talk.categories');

            if ($categories === null) {
                $categories = [
                    'api'                => 'APIs (REST, SOAP, etc.)',
                    'continuousdelivery' => 'Continuous Delivery',
                    'database'           => 'Database',
                    'development'        => 'Development',
                    'devops'             => 'Devops',
                    'framework'          => 'Framework',
                    'ibmi'               => 'IBMi',
                    'javascript'         => 'JavaScript',
                    'security'           => 'Security',
                    'testing'            => 'Testing',
                    'uiux'               => 'UI/UX',
                    'other'              => 'Other',
                ];
            }

            $levels = $app->config('talk.levels');

            if ($levels === null) {
                $levels = [
                    'entry'    => 'Entry level',
                    'mid'      => 'Mid-level',
                    'advanced' => 'Advanced',
                ];
            }

            $types = $app->config('talk.types');

            if ($types === null) {
                $types = [
                    'regular'  => 'Regular',
                    'tutorial' => 'Tutorial',
                ];
            }

            return new TalkHelper(
                $categories,
                $levels,
                $types
            );
        };
    }
}
