<?php

namespace OpenCFP\Provider;

use OpenCFP\Domain\Services\ResetEmailer;
use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use Twig_Environment;

class ResetEmailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['reset_emailer'] = function ($app) {
            /* @var Twig_Environment $twig */
            $twig = $app['twig'];

            return new ResetEmailer(
                $app['mailer'],
                $twig->loadTemplate('emails/reset_password.twig'),
                $app->config('application.email'),
                $app->config('application.title')
            );
        };
    }
}
