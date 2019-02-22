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

namespace OpenCFP\Test\Integration\Provider;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Test\Integration\WebTestCase;

final class SentinelServiceProviderTest extends WebTestCase
{
    /**
     * @test
     */
    public function allRepositoriesAreSet()
    {
        /** @var Sentinel $sentinel */
        $sentinel = $this->container->get(Sentinel::class);

        $this->assertNotNull($sentinel->getUserRepository());
        $this->assertNotNull($sentinel->getActivationRepository());
        $this->assertNotNull($sentinel->getRoleRepository());
        $this->assertNotNull($sentinel->getReminderRepository());
        $this->assertNotNull($sentinel->getPersistenceRepository());
        $this->assertNotNull($sentinel->getThrottleRepository());
    }
}
