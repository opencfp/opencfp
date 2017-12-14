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

use OpenCFP\Domain\Services\ProfileImageProcessor;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class ImageProcessorProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['profile_image_processor'] = function ($app) {
            return new ProfileImageProcessor($app['path']->uploadToPath(), $app['security.random']);
        };
    }
}
