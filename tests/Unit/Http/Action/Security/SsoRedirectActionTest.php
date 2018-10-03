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
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class SsoRedirectActionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function redirectsToLoginWhenUnableToAuthenticateToOpenCFPCentral(): void
    {
        $clientId     = 1;
        $clientSecret = 'secret';
        $redirectUri  = '/redirect';
        $resourceUrl  = '/resource';
        $tokenUrl     = '/tokenUrl';

        $sentinel = $this->prophesize(\Cartalyst\Sentinel\Sentinel::class);
        $accounts = $this->prophesize(Services\AccountManagement::class);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);
        $urlGenerator
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
            $sentinel->reveal(),
            $accounts->reveal(),
            $urlGenerator->reveal(),
            $clientId,
            $clientSecret,
            $redirectUri,
            $resourceUrl,
            $tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function useExistingUser(): void
    {
        $clientId     = 1;
        $clientSecret = 'secret';
        $redirectUri  = '/redirect';
        $resourceUrl  = '/resource';
        $tokenUrl     = '/tokenUrl';
        $email        = $this->faker()->email;

        $sentinel = $this->prophesize(\Cartalyst\Sentinel\Sentinel::class);

        $user = $this->createUserDouble();

        $accounts = $this->prophesize(Services\AccountManagement::class);
        $accounts
            ->findByLogin($email)
            ->willReturn($user);

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);
        $urlGenerator
            ->generate('dashboard')
            ->willReturn('/dashboard');

        $httpClient = $this->createHttpClientDouble($email);

        $request        = $this->prophesize(HttpFoundation\Request::class);
        $redirectAction = new SsoRedirectAction(
            $sentinel->reveal(),
            $accounts->reveal(),
            $urlGenerator->reveal(),
            $clientId,
            $clientSecret,
            $redirectUri,
            $resourceUrl,
            $tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function createUserIfOneDoesNotExist()
    {
        $clientId     = 1;
        $clientSecret = 'secret';
        $redirectUri  = '/redirect';
        $resourceUrl  = '/resource';
        $tokenUrl     = '/tokenUrl';
        $email        = $this->faker()->email;

        $sentinel = $this->prophesize(\Cartalyst\Sentinel\Sentinel::class);

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

        $urlGenerator = $this->prophesize(Routing\Generator\UrlGeneratorInterface::class);
        $urlGenerator
            ->generate('dashboard')
            ->willReturn('/dashboard');

        $httpClient = $this->createHttpClientDouble($email);

        $request        = $this->prophesize(HttpFoundation\Request::class);
        $redirectAction = new SsoRedirectAction(
            $sentinel->reveal(),
            $accounts,
            $urlGenerator->reveal(),
            $clientId,
            $clientSecret,
            $redirectUri,
            $resourceUrl,
            $tokenUrl,
            $httpClient
        );

        $response = $redirectAction($request->reveal());
        $this->assertSame(HttpFoundation\Response::HTTP_FOUND, $response->getStatusCode());
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
