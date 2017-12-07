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

namespace OpenCFP\Test\Unit\Provider;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Test\BaseTestCase;

/**
 * @covers \OpenCFP\Provider\SentinelServiceProvider
 */
final class SentinelServiceProviderTest extends BaseTestCase
{
    public function testAllRepositoriesAreSet()
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
