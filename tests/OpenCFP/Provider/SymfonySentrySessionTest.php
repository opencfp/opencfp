<?php

namespace OpenCFP\Test\Provider;

use OpenCFP\Provider\SymfonySentrySession;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SymfonySentrySessionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $sentrySession = new SymfonySentrySession($this->getSessionMock());

        $this->assertSame('cartalyst_sentry', $sentrySession->getKey());
    }

    public function testConstructorSetsKey()
    {
        $key = 'foo';

        $sentrySession = new SymfonySentrySession(
            $this->getSessionMock(),
            $key
        );

        $this->assertSame($key, $sentrySession->getKey());
    }

    public function testPutSetsValue()
    {
        $key = 'foo';
        $value = 'bar';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->identicalTo($key),
                $this->identicalTo($value)
            )
        ;

        $sentrySession = new SymfonySentrySession(
            $session,
            $key
        );

        $sentrySession->put($value);
    }

    public function testGetReturnsValue()
    {
        $key = 'foo';
        $value = 'bar';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($key))
            ->willReturn($value)
        ;

        $sentrySession = new SymfonySentrySession(
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
            ->with($this->identicalTo($key))
        ;

        $sentrySession = new SymfonySentrySession(
            $session,
            $key
        );

        $sentrySession->forget();
    }

    //
    // Factory Methods
    //

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private function getSessionMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')->getMock();
    }
}
