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
use OpenCFP\Application;
use OpenCFP\Environment;

/**
 * @covers \OpenCFP\Provider\SentinelServiceProvider
 */
final class SentinelServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function allRepositoriesAreSet()
    {
        //The Reminder and Throttle repositories aren't set in the controller.
        //This allows us to make sure they are properly set
        $app                 = new Application(__DIR__ . '/../../..', Environment::testing());
        $app['session.test'] = true;
        /** @var Sentinel $sentinel */
        $sentinel = $app[Sentinel::class];

        $this->assertNotNull($sentinel->getUserRepository());
        $this->assertNotNull($sentinel->getActivationRepository());
        $this->assertNotNull($sentinel->getRoleRepository());
        $this->assertNotNull($sentinel->getReminderRepository());
        $this->assertNotNull($sentinel->getPersistenceRepository());
        $this->assertNotNull($sentinel->getThrottleRepository());
    }
}
