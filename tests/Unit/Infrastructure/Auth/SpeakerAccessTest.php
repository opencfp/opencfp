<?php

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\SpeakerAccess;
use OpenCFP\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversNothing
 */
class SpeakerAccessTest extends WebTestCase
{
    public function testReturnsRedirectIfCheckFailed()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);
        $this->swap(Authentication::class, $auth);
        $this->assertInstanceOf(RedirectResponse::class, SpeakerAccess::userHasAccess($this->app));
    }

    public function testReturnsNothingIfUserIsLoggedIn()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $this->swap(Authentication::class, $auth);
        $this->assertNull(SpeakerAccess::userHasAccess($this->app));
    }

    public function testAnAdminHasAccessToSpeakerPages()
    {
        $this->asAdmin();
        $this->assertNull(SpeakerAccess::userHasAccess($this->app));
    }
}
