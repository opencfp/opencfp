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

namespace OpenCFP\Provider\Gateways;

use OpenCFP\Console\Application;
use OpenCFP\Console\Command\ClearCacheCommand;
use OpenCFP\Console\Command\UserCreateCommand;
use OpenCFP\Console\Command\UserDemoteCommand;
use OpenCFP\Console\Command\UserPromoteCommand;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Path;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class ConsoleGatewayProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[ClearCacheCommand::class] = function ($app) {
            /** @var Path $path */
            $path = $app['path'];

            return new ClearCacheCommand([
                $path->cachePurifierPath(),
                $path->cacheTwigPath(),
            ]);
        };

        $app[UserCreateCommand::class] = function ($app) {
            return new UserCreateCommand(
                $app[AccountManagement::class]
            );
        };

        $app[UserDemoteCommand::class] = function ($app) {
            return new UserDemoteCommand(
                $app[AccountManagement::class]
            );
        };

        $app[UserPromoteCommand::class] = function ($app) {
            return new UserPromoteCommand(
                $app[AccountManagement::class]
            );
        };

        $app[Application::class] = function ($app) {
            $console = new Application();

            $console->setDispatcher($app['dispatcher']);
            $console->addCommands([
                $app[ClearCacheCommand::class],
                $app[UserCreateCommand::class],
                $app[UserDemoteCommand::class],
                $app[UserPromoteCommand::class],
            ]);

            return $console;
        };
    }
}
