<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Http\Action\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services;
use OpenCFP\Http\Action\Security\SsoRedirectAction;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use PHPUnit\Framework;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class SsoRedirectActionTest extends Framework\TestCase
{
    use Helper;

    private $clientId;

    private $clientSecret;

    private $redirectUri;

    private $resourceUrl;

    private $tokenUrl;

    /** @var ObjectProphecy */
    private $sentinel;

    /** @var ObjectProphecy */
    private $accounts;

    /** @var ObjectProphecy */
    private $urlGenerator;

    public function setUp()
    {
        parent::setUp();

        $this->clientId     = 1;
        $this->clientSecret = 'secret';
        $this->redirectUri  = '/redirect';
        $this->resourceUrl  = '/resource';
        $this->tokenUrl     = '/tokenUrl';

        $this->sentinel     = $this->prophesize(\Cartalyst\Sentinel\Sentinel::class);
        $this->accounts     = $this->prophesize(Services\AccountManagement::class);
        $this->urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);
    }

    /**
     * @test
     */
    public function redirectsToLoginWhenUnableToAuthenticateToOpenCFPCentral(): void
    {
        $this->urlGenerator
            ->generate('login')
            ->willReturn('/login');

        $exceptionMessage = 'We were unable to authenticate with OpenCFP Central. Please try again';
        $mh               = new MockHandler([
            new RequestException('test', new Request('POST', 'test')),
        ]);
        $handler    = HandlerStack::create($mh);
        $httpClient = new Client(['handler' => $handler]);

        $session = $this->prophesize(HttpFoundation\Session\SessionInterface::class);
        $session
            ->set(
                Argument::exact('flash'),
                Argument::exact([
                    'type'  => 'error',
                    'short' => 'Error',
                    'ext'   => $exceptionMessage,
                ])
            )
            ->shouldBeCalled();

        $request = $this->prophesize(HttpFoundation\Request::class);
        $request
            ->getSession()
            ->willReturn($session->reveal());

        $redirectAction = new SsoRedirectAction(
            $this->sentinel->reveal(),
            $this->accounts->reveal(),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->resourceUrl,
            $this->tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertContains('Redirecting to /login', $response->getContent());
    }

    /**
     * @test
     */
    public function useExistingUser(): void
    {
        $user  = $this->createUserDouble();
        $email = $this->faker()->email;

        $accounts = $this->prophesize(Services\AccountManagement::class);
        $accounts
            ->findByLogin($email)
            ->willReturn($user);

        $this->urlGenerator
            ->generate('dashboard')
            ->willReturn('/dashboard');

        $httpClient = $this->createHttpClientDouble($email);

        $request        = $this->prophesize(HttpFoundation\Request::class);
        $redirectAction = new SsoRedirectAction(
            $this->sentinel->reveal(),
            $accounts->reveal(),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->resourceUrl,
            $this->tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertContains('Redirecting to /dashboard', $response->getContent());
    }

    /**
     * @test
     */
    public function createUserIfOneDoesNotExist()
    {
        $user = $this->createUserDouble();

        // Mockery better handles the sequence of calls we need for the test
        $accounts = \Mockery::mock(Services\AccountManagement::class);
        $accounts
            ->shouldReceive('findByLogin')
            ->once()
            ->andThrow(UserNotFoundException::class);
        $accounts
            ->shouldReceive('findByLogin')
            ->once()
            ->andReturn($user);
        $accounts->shouldReceive('create');
        $accounts->shouldReceive('activate');

        $this->urlGenerator
            ->generate('dashboard')
            ->willReturn('/dashboard');

        $httpClient = $this->createHttpClientDouble($this->faker()->email);

        $request        = $this->prophesize(HttpFoundation\Request::class);
        $redirectAction = new SsoRedirectAction(
            $this->sentinel->reveal(),
            $accounts,
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->resourceUrl,
            $this->tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertContains('Redirecting to /dashboard', $response->getContent());
    }

    /**
     * @test
     */
    public function redirectsIfPostReceivesInvalidJsonReceived(): void
    {
        $this->urlGenerator
            ->generate('login')
            ->willReturn('/login');

        $postResponse = new class() {
            public function getBody()
            {
                return 'THISISNOTVALIDJSON';
            }
        };

        $httpClient = $this->prophesize(Client::class);
        $httpClient
            ->post()
            ->willReturn($postResponse);

        $request = $this->prophesize(HttpFoundation\Request::class);
        $session = new class() {
            public function set()
            {
                return null;
            }
        };
        $request
            ->getSession()
            ->shouldBeCalled()
            ->willReturn($session);

        $redirectAction = new SsoRedirectAction(
            $this->sentinel->reveal(),
            $this->accounts->reveal(),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->resourceUrl,
            $this->tokenUrl,
            $httpClient->reveal()
        );

        $response = $redirectAction($request->reveal());
        $this->assertContains('Redirecting to /login', $response->getContent());
    }

    /**
     * @test
     */
    public function redirectIfCentralAuthAttemptReturnsInvalidJson()
    {
        $this->urlGenerator
            ->generate('login')
            ->willReturn('/login');
        $postResponse = new class() {
            public function getBody(): string
            {
                return '{"access_token": "test_token"}';
            }
        };
        $userResponse = new class() {
            public function get(): string
            {
                return 'THISISNOTVALIDJSON';
            }
        };

        $httpClient = $this->prophesize(Client::class);
        $httpClient
            ->post()
            ->willReturn($postResponse);
        $httpClient
            ->get()
            ->willReturn($userResponse);

        $request = $this->prophesize(HttpFoundation\Request::class);
        $session = new class() {
            public function set()
            {
                return null;
            }
        };
        $request
            ->getSession()
            ->willReturn($session);

        $redirectAction = new SsoRedirectAction(
            $this->sentinel->reveal(),
            $this->accounts->reveal(),
            $this->urlGenerator->reveal(),
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->resourceUrl,
            $this->tokenUrl,
            $httpClient->reveal()
        );

        $response = $redirectAction($request->reveal());
        $this->assertContains('Redirecting to /login', $response->getContent());
    }

    private function createUserDouble(): \Mockery\MockInterface
    {
        $returnedUser = \Mockery::mock(\Cartalyst\Sentinel\Users\UserInterface::class);
        $user         = \Mockery::mock(\OpenCFP\Infrastructure\Auth\UserInterface::class);
        $user
            ->shouldReceive('getUser')
            ->andReturn($returnedUser);

        return $user;
    }

    private function createHttpClientDouble($email)
    {
        $postResponse = new class() {
            public function getBody()
            {
                return '{"access_token": "test_token"}';
            }
        };
        $getResponse = new class($email) {
            protected $email;

            public function __construct($email)
            {
                $this->email = $email;
            }

            public function getBody()
            {
                return '{"email": "' . $this->email . '"}';
            }
        };
        $httpClient = $this->prophesize(Client::class);
        $httpClient
            ->post(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($postResponse);
        $httpClient
            ->get(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($getResponse);

        return $httpClient->reveal();
    }
}
