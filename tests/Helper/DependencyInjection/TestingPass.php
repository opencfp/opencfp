<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Helper\DependencyInjection;

use Cartalyst\Sentinel\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\IdentityProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TestingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getAlias(Authentication::class)->setPublic(true);
        $container->getAlias(IdentityProvider::class)->setPublic(true);

        $container->getAlias(Capsule::class)->setPublic(true);
        $container->getDefinition(CallForPapers::class)->setPublic(true);
        $container->getDefinition(Sentinel::class)->setPublic(true);
    }
}
