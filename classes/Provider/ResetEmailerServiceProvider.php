<?php

namespace OpenCFP\Provider;

use OpenCFP\Domain\Services\ResetEmailer;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Twig_Environment;

class ResetEmailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['reset_emailer'] = $app->share(function ($app) {
            /* @var Twig_Environment $twig */
            $twig = $app['twig'];

            return new ResetEmailer(
                $app['mailer'],
                $twig->loadTemplate('emails/reset_password.twig'),
                $app->config('application.email'),
                $app->config('application.title')
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
