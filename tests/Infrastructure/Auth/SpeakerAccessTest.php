<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Mockery;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\SpeakerAccess;
use OpenCFP\Test\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SpeakerAccessTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnsRedirectIfCheckFailed()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);
        $this->swap(Authentication::class, $auth);
        $this->assertInstanceOf(RedirectResponse::class, SpeakerAccess::userHasAccess($this->app));
    }

    /**
     * @test
     */
    public function testReturnsNothingIfUserIsLoggedIn()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $this->swap(Authentication::class, $auth);
        $this->assertNull(SpeakerAccess::userHasAccess($this->app));
    }

    /**
     * @test
     */
    public function testAnAdminHasAccessToSpeakerPages()
    {
        $this->asAdmin();
        $this->assertNull(SpeakerAccess::userHasAccess($this->app));
    }
}
