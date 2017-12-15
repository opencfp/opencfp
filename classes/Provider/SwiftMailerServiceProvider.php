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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swift_Mailer;
use Swift_SmtpTransport;

final class SwiftMailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['mailer'] = function ($app) {
            $transport = (new Swift_SmtpTransport($app->config('mail.host'), $app->config('mail.port')))
                ->setUsername($app->config('mail.username'))
                ->setPassword($app->config('mail.password'));

            return new Swift_Mailer($transport);
        };
    }
}
