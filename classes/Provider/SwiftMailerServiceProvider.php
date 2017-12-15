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

use OpenCFP\Environment;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swift_Mailer;
use Swift_NullTransport;
use Swift_SmtpTransport;

final class SwiftMailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['mailer.transport'] = function ($app) {
            /** @var Environment $environment */
            $environment = $app['env'];

            if ($environment->isTesting()) {
                return new Swift_NullTransport();
            }

            $transport = new Swift_SmtpTransport(
                $app->config('mail.host'),
                $app->config('mail.port')
            );

            $transport
                ->setUsername($app->config('mail.username'))
                ->setPassword($app->config('mail.password'));

            return $transport;
        };

        $app['mailer'] = function ($app) {
            return new Swift_Mailer($app['mailer.transport']);
        };
    }
}
