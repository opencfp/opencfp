<?php

namespace OpenCFP\Test\Unit\Provider;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Application;
use OpenCFP\Environment;

/**
 * @covers \OpenCFP\Provider\SentinelServiceProvider
 */
class SentinelServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testAllRepositoriesAreSet()
    {
        //The Reminder and Throttle repositories aren't set in the controller.
        //This allows us to make sure they are properly set
        $app                 = new Application(BASE_PATH, Environment::testing());
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
