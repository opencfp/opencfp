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

namespace OpenCFP\Test\Integration\Infrastructure\Event;

use Mockery;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Infrastructure\Event\TwigGlobalsListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;

final class TwigGlobalsListenerTest extends TestCase
{
    /**
     * @dataProvider provideTestSetup
     *
     * @test
     */
    public function globals(Authentication $authentication, bool $isOpen, string $uri, string $flash = null, string $fixture)
    {
        $twig    = new Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . '/Fixtures'));
        $session = new Session(new MockArraySessionStorage());

        if ($flash !== null) {
            $session->set('flash', $flash);
        }

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new TwigGlobalsListener(
            $authentication,
            $this->mockCallForPapers($isOpen),
            $session,
            $twig
        ));

        $eventDispatcher->dispatch(KernelEvents::REQUEST, new GetResponseEvent(
            Mockery::mock(HttpKernelInterface::class),
            Request::create($uri),
            HttpKernelInterface::MASTER_REQUEST
        ));

        $output = $twig->render('globals.txt.twig');

        $this->assertStringEqualsFile(__DIR__ . '/Fixtures/' . $fixture, $output);
    }

    public function provideTestSetup(): array
    {
        return [
            [$this->mockAdmin(), true, '/foo', null, 'global_with_admin.txt'],
            [$this->mockReviewer(), true, '/foo', 'You\'ve got mail.', 'global_with_reviewer.txt'],
            [$this->mockAnonymous(), false, '/bar', null, 'global_with_anonymous_user.txt'],
        ];
    }

    private function mockAdmin(): Authentication
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(42);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $auth = Mockery::mock(Authentication::class);

        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        return $auth;
    }

    private function mockReviewer(): Authentication
    {
        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('getId')->andReturn(43);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(true);
        $auth = Mockery::mock(Authentication::class);

        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);

        return $auth;
    }

    private function mockAnonymous(): Authentication
    {
        $auth = Mockery::mock(Authentication::class);

        $auth->shouldReceive('isAuthenticated')->andReturn(false);
        $auth->shouldReceive('user')->andReturn(null);

        return $auth;
    }

    private function mockCallForPapers(bool $isOpen): CallForPapers
    {
        $cfp = Mockery::mock(CallForPapers::class);
        $cfp->shouldReceive('isOpen')->andReturn($isOpen);

        return $cfp;
    }
}
