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

namespace OpenCFP\Test\Unit;

use Mockery as m;
use OpenCFP\Application;

/**
 * @covers \OpenCFP\ContainerAware
 */
class ContainerAwareTest extends \PHPUnit\Framework\TestCase
{
    public function testAllowsToRetrieveService()
    {
        $slug    = 'foo';
        $service = 'bar';

        $application = $this->getApplicationMock();

        $application
            ->shouldReceive('offsetGet')
            ->once()
            ->with($slug)
            ->andReturn($service);

        $containerAware = new ContainerAwareFake();

        $containerAware->setApplication($application);

        $this->assertSame($service, $containerAware->getService($slug));
    }

    //
    // Factory Method
    //

    /**
     * @return Application|m\MockInterface
     */
    private function getApplicationMock(): Application
    {
        return m::mock(Application::class);
    }
}
