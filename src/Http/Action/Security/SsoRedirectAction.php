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

namespace OpenCFP\Http\Action\Security;

use Alpha\A;
use Cartalyst\Sentinel\Sentinel;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class SsoRedirectAction
{
    /** @var AccountManagement */
    private $accounts;

    /** @var Sentinel */
    private $sentinel;

    /** @var Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string */
    private $redirectUri;

    /** @var string */
    private $resourceUri;

    /** @var string */
    private $tokenUrl;

    /** @var ClientInterface */
    private $httpClient;

    public function __construct(
        Sentinel $sentinel,
        AccountManagement $accounts,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $resourceUri,
        string $tokenUrl,
        ClientInterface $httpClient
    ) {
        $this->sentinel     = $sentinel;
        $this->accounts     = $accounts;
        $this->urlGenerator = $urlGenerator;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri  = $redirectUri;
        $this->resourceUri  = $resourceUri;
        $this->tokenUrl     = $tokenUrl;
        $this->httpClient   = $httpClient;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->tokenUrl,
                [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => $this->redirectUri,
                    'code'          => $request->get('code'),
                ], 'verify' => false,
            ]
            );
        } catch (RequestException $e) {
            return $this->redirectToLogin($request);
        }

        /**
         * What do we do with the access token?
         * Check if the user exists
         * -> if they don't, generate a random password and then create and activate an account
         * manually log them in
         * redirect them to their dashboard
         */
        $details = \json_decode((string) $response->getBody(), true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            return $this->redirectToLogin($request);
        }

        $userResponse = $this->httpClient->get($this->resourceUri, [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $details['access_token'],
            ],
            'verify' => false,
        ]);
        $userDetails = \json_decode((string) $userResponse->getBody(), true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            return $this->redirectToLogin($request);
        }

        try {
            /** @var SentinelUser $user */
            $user = $this->accounts->findByLogin($userDetails['email']);
        } catch (UserNotFoundException $e) {
            $this->accounts->create(
                $userDetails['email'],
                \uniqid('opencfp', true),
                ['activated' => 1]
            );
            $this->accounts->activate($userDetails['email']);
            $user = $this->accounts->findByLogin($userDetails['email']);
        }

        $this->sentinel->login($user->getUser());
        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }

    private function redirectToLogin(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $request->getSession()->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => 'We were unable to authenticate with OpenCFP Central. Please try again',
        ]);
        $url = $this->urlGenerator->generate('login');

        return new HttpFoundation\RedirectResponse($url);
    }
}
