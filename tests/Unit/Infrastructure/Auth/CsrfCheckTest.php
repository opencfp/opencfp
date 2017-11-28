<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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

    public function testReturnsTrueWhenTokenMangerReturnsTrue()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(true);

        $csrf = new CsrfValidator($manager);
        $this->assertTrue($csrf->isValid(new Request()));
    }

    public function testReturnsFalseWhenTokenManagersReturnsFalse()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(false);

        $csrf = new CsrfValidator($manager);
        $this->assertFalse($csrf->isValid(new Request()));
    }
}
