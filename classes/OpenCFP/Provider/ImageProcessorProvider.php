<?php

namespace OpenCFP\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use OpenCFP\Domain\Services\ProfileImageProcessor;

class ImageProcessorProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['profile_image_processor'] = $app->share(function($app) {
            return new ProfileImageProcessor($app->uploadPath(), $app['security.random']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}