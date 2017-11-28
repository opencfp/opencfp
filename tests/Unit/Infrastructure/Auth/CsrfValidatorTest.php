<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Domain\Services\RequestValidator;
use OpenCFP\Infrastructure\Auth\CsrfValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * @covers \OpenCFP\Infrastructure\Auth\CsrfValidator
 */
class CsrfValidatorTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(CsrfValidator::class);
        $this->assertTrue($reflection->isFinal());
    }

    public function testIsInstanceOfRequestValidator()
    {
        $csrf = new CsrfValidator(Mockery::mock(CsrfTokenManager::class));
        $this->assertInstanceOf(RequestValidator::class, $csrf);
    }

    public function testReturnsTrueWhenTokenMangerReturnsTrue()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(true);
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->once()->with('token_id');
        $request->shouldReceive('get')->once()->with('token');

        $csrf = new CsrfValidator($manager);
        $this->assertTrue($csrf->isValid($request));
    }

    public function testReturnsFalseWhenTokenManagersReturnsFalse()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(false);
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('get')->once()->with('token_id');
        $request->shouldReceive('get')->once()->with('token');

        $csrf = new CsrfValidator($manager);
        $this->assertFalse($csrf->isValid($request));
    }
}
