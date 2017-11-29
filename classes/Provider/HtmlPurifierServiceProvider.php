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

use HTMLPurifier;
use HTMLPurifier_Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HtmlPurifierServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['purifier'] = function ($app) {
            $config = HTMLPurifier_Config::createDefault();

            if ($app->config('cache.enabled')) {
                $cachePermissions = 0755;
                $config->set('Cache.SerializerPermissions', $cachePermissions);
                $cacheDirectory = $app['path']->cachePurifierPath();

                if (!\is_dir($cacheDirectory)) {
                    \mkdir($cacheDirectory, $cachePermissions, true);
                }

                $config->set('Cache.SerializerPath', $cacheDirectory);
            }

            return new HTMLPurifier($config);
        };
    }
}
