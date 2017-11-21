<?php

namespace OpenCFP\Test\Unit\Http\Controller;

use OpenCFP\Application;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @coversNothing
 */
class FlashableTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFlashReturnsFlashAndClearsIt()
    {
        $flash = 'foo';

        $session = $this->getSessionMock();

        $session
            ->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo('flash'))
            ->willReturn($flash)
        ;

        $session
            ->expects($this->at(1))
            ->method('set')
            ->with(
                $this->identicalTo('flash'),
                $this->identicalTo(null)
            )
        ;

        $application = $this->getApplicationMock([
            'session' => $session,
        ]);

        $flashable = new FlashableTraitFake();

        $this->assertSame($flash, $flashable->getFlash($application));
    }

    public function testClearFlashClearsFlash()
    {
        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo('flash'),
                $this->identicalTo(null)
            )
        ;

        $application = $this->getApplicationMock([
            'session' => $session,
        ]);

        $flashable = new FlashableTraitFake();

        $flashable->clearFlash($application);
    }

    //
    // Factory Methods
    //

    /**
     * @param array $items
     *
     * @return Application|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getApplicationMock(array $items = [])
    {
        $application = $this->getMockBuilder(\OpenCFP\Application::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $application
            ->expects($this->any())
            ->method('offsetGet')
            ->willReturnCallback(function ($alias) use ($items) {
                if (array_key_exists($alias, $items)) {
                    return $items[$alias];
                }
            })
        ;

        return $application;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private function getSessionMock()
    {
        return $this->getMockBuilder(\Symfony\Component\HttpFoundation\Session\SessionInterface::class)->getMock();
    }
}
