<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenCFP\Infrastructure\Auth\CsrfCheck;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * @covers \OpenCFP\Infrastructure\Auth\CsrfCheck
 */
class CsrfCheckTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function testNothingHappensWhenTokenIsValid()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(true);

        $csrf = new CsrfCheck($manager);
        $this->assertNull($csrf->checkCsrf('bla', 'bla'));
    }

    public function testIsRedirectWhenTokenIsNotValid()
    {
        $manager = Mockery::mock(CsrfTokenManager::class);
        $manager->shouldReceive('isTokenValid')->andReturn(false);

        $csrf = new CsrfCheck($manager);
        $this->assertInstanceOf(RedirectResponse::class, $csrf->checkCsrf('bla', 'bla'));
    }
}
