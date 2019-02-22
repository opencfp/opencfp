<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Localheinz\Test\Util\Helper;
use Mockery;
use OpenCFP\Infrastructure\Auth\SymfonySentinelSession;

final class SymfonySentinelSessionTest extends \PHPUnit\Framework\TestCase
{
    use Helper;
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function implementsSessionInterface()
    {
        $this->assertClassImplementsInterface(\Cartalyst\Sentinel\Sessions\SessionInterface::class, SymfonySentinelSession::class);
    }

    /**
     * @test
     */
    public function putSetsValue()
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

        $sentinelSession = new SymfonySentinelSession(
            $session,
            $key
        );

        $sentinelSession->put($value);
    }

    /**
     * @test
     */
    public function getReturnsValue()
    {
        $key   = 'foo';
        $value = 'bar';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($key))
            ->willReturn($value);

        $sentinelSession = new SymfonySentinelSession(
            $session,
            $key
        );

        $this->assertSame($value, $sentinelSession->get());
    }

    /**
     * @test
     */
    public function forgetRemovesKey()
    {
        $key = 'foo';

        $session = $this->getSessionMock();

        $session
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($key));

        $sentinelSession = new SymfonySentinelSession(
            $session,
            $key
        );

        $sentinelSession->forget();
    }

    private function getSessionMock()
    {
        return $this->getMockBuilder(\Symfony\Component\HttpFoundation\Session\SessionInterface::class)->getMock();
    }
}
