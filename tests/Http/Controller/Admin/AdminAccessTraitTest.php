<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;
use OpenCFP\Application;

class AdminAccessTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsFalseIfCheckFailed()
    {
        $sentry = $this->getSentryMock();

        $sentry
            ->expects($this->once())
            ->method('check')
            ->willReturn(false)
        ;

        $sentry
            ->expects($this->never())
            ->method('getUser')
        ;

        $application = $this->getApplicationMock([
            'sentry' => $sentry,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertFalse($adminAccess->hasAdminAccess());
    }

    public function testReturnsFalseIfCheckSucceededButUserHasNoAdminPermission()
    {
        $userWithoutAdminPermission = $this->getUserMock(false);

        $sentry = $this->getSentryMock();

        $sentry
            ->expects($this->at(0))
            ->method('check')
            ->willReturn(true)
        ;

        $sentry
            ->expects($this->at(1))
            ->method('getUser')
            ->willReturn($userWithoutAdminPermission)
        ;

        $application = $this->getApplicationMock([
            'sentry' => $sentry,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertFalse($adminAccess->hasAdminAccess());
    }

    public function testReturnsTrueIfCheckSucceededAndUserHasAdminPermission()
    {
        $userWithAdminPermission = $this->getUserMock(true);

        $sentry = $this->getSentryMock();

        $sentry
            ->expects($this->at(0))
            ->method('check')
            ->willReturn(true)
        ;

        $sentry
            ->expects($this->at(1))
            ->method('getUser')
            ->willReturn($userWithAdminPermission)
        ;

        $application = $this->getApplicationMock([
            'sentry' => $sentry,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertTrue($adminAccess->hasAdminAccess());
    }

    //
    // Factory Methods
    //

    /**
     * @param array $items
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
     * @return \PHPUnit_Framework_MockObject_MockObject|Sentry
     */
    private function getSentryMock()
    {
        return $this->getMockBuilder('Cartalyst\Sentry\Sentry')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @param bool $hasAdminPermission
     * @return UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserMock($hasAdminPermission = false)
    {
        $user = $this->getMockBuilder('Cartalyst\Sentry\Users\UserInterface')->getMock();

        $user
            ->expects($this->any())
            ->method('hasPermission')
            ->with($this->identicalTo('admin'))
            ->willReturn($hasAdminPermission)
        ;

        return $user;
    }
}
