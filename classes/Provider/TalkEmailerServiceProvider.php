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

use OpenCFP\Domain\Services\TalkEmailer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Swift_Mailer;
use Swift_SmtpTransport;

final class TalkEmailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $options = [
            'host'     => $app->config('mail.host'),
            'port'     => $app->config('mail.port'),
            'username' => $app->config('mail.username'),
            'password' => $app->config('mail.password'),
        ];
        $transport = (new Swift_SmtpTransport($options['host'], $options['port']))
            ->setUsername($options['username'])
            ->setPassword($options['password']);
        $swiftMailer         = new Swift_Mailer($transport);
        $app['talk_emailer'] = function ($app) use ($options, $swiftMailer) {
            return new TalkEmailer($swiftMailer);
        };
    }
}
