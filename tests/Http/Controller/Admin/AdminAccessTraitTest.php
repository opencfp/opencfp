<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;
use Mockery;
use OpenCFP\Application;
use OpenCFP\Domain\Services\Authentication;

class AdminAccessTraitTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testReturnsFalseIfCheckFailed()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);

        $application = $this->getApplicationMock([
            Authentication::class => $auth,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertFalse($adminAccess->hasAdminAccess());
    }

    public function testReturnsFalseIfCheckSucceededButUserHasNoAdminPermission()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $application = $this->getApplicationMock([
            Authentication::class => $auth,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertFalse($adminAccess->hasAdminAccess());
    }

    public function testReturnsTrueIfCheckSucceededAndUserHasAdminPermission()
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);

        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        $application = $this->getApplicationMock([
            Authentication::class => $auth,
        ]);

        $adminAccess = new AdminAccessTraitFake($application);

        $this->assertTrue($adminAccess->hasAdminAccess());
    }

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
}
