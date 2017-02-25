<?php

namespace OpenCFP\Test;

use Mockery as m;
use OpenCFP\Application;

class ContainerAwareTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testAllowsToRetrieveService()
    {
        $slug = 'foo';
        $service = 'bar';

        $application = $this->getApplicationMock();

        $application
            ->shouldReceive('offsetGet')
            ->once()
            ->with($slug)
            ->andReturn($service)
        ;

        $containerAware = new ContainerAwareFake();

        $containerAware->setApplication($application);

        $this->assertSame($service, $containerAware->getService($slug));
    }

    //
    // Factory Method
    //

    /**
     * @return m\MockInterface|Application
     */
    private function getApplicationMock()
    {
        return m::mock(Application::class);
    }
}
