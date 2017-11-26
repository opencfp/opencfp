<?php

namespace OpenCFP\Test\Unit\Provider;

use Mockery;
use OpenCFP\Provider\SymfonySentinelSession;

/**
 * @covers \OpenCFP\Provider\SymfonySentinelSession
 */
class SymfonySentinelSessionTest extends \PHPUnit\Framework\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testDefaults()
    {
        $session = new SymfonySentinelSession($this->getSessionMock());
        $this->assertInstanceOf(\Cartalyst\Sentinel\Sessions\SessionInterface::class, $session);
    }

    public function testPutSetsValue()
    {
        $key   = 'foo';
        $value = 'bar';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo($key),
                $this->identicalTo($value)
            );

        $sentrySession = new SymfonySentinelSession(
            $session,
            $key
        );

        $sentrySession->put($value);
    }

    public function testGetReturnsValue()
    {
        $key   = 'foo';
        $value = 'bar';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($key))
            ->willReturn($value);

        $sentrySession = new SymfonySentinelSession(
            $session,
            $key
        );

        $this->assertSame($value, $sentrySession->get());
    }

    public function testForgetRemovesKey()
    {
        $key = 'foo';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($key));

        $sentrySession = new SymfonySentinelSession(
            $session,
            $key
        );

        $sentrySession->forget();
    }

    private function getSessionMock()
    {
        return $this->getMockBuilder(\Symfony\Component\HttpFoundation\Session\SessionInterface::class)->getMock();
    }
}
