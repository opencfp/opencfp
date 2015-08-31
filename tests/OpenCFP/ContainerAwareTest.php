<?php

namespace OpenCFP;

use OpenCFP\Application;

class ContainerAwareTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowsToRetrieveService()
    {
        $slug = 'foo';
        $service = 'bar';

        $application = $this->getApplicationMock();

        $application
            ->expects($this->once())
            ->method('offsetGet')
            ->with($this->identicalTo($slug))
            ->willReturn($service)
        ;

        $containerAware = new ContainerAwareFake();

        $containerAware->setApplication($application);

        $this->assertSame($service, $containerAware->getService($slug));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Application
     */
    private function getApplicationMock()
    {
        return $this->getMockBuilder('OpenCFP\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
