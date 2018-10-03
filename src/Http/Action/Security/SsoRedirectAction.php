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

namespace OpenCFP\Http\Action\Security;

use Cartalyst\Sentinel\Sentinel;
use GuzzleHttp\Client;
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

    /** @var int */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string */
    private $redirectUri;

    /** @var string */
    private $resourceUri;

    /** @var string */
    private $tokenUrl;

    /** @var Client */
    private $httpClient;

    /**
     * SsoRedirectAction constructor.
     *
     * @param Sentinel                                $sentinel
     * @param AccountManagement                       $accounts
     * @param Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param int                                     $clientId
     * @param string                                  $clientSecret
     * @param string                                  $redirectUri
     * @param string                                  $resourceUri
     * @param string                                  $tokenUrl
     * @param Client                                  $httpClient
     */
    public function __construct(
        Sentinel $sentinel,
        AccountManagement $accounts,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        int $clientId,
        string $clientSecret,
        string $redirectUri,
        string $resourceUri,
        string $tokenUrl,
        Client $httpClient
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

    /**
     * @param HttpFoundation\Request $request
     *
     * @throws \OpenCFP\Infrastructure\Auth\UserExistsException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \OpenCFP\Infrastructure\Auth\UserNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return HttpFoundation\Response
     */
    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $response = $this->httpClient->post($this->tokenUrl, [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => $this->redirectUri,
                    'code'          => $request->get('code'),
                ],
            ]);
        } catch (\Exception $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'We were unable to authenticate with OpenCFP Central. Please try again',
            ]);
            $url = $this->urlGenerator->generate('login');

            return new HttpFoundation\RedirectResponse($url);
        }

        /**
         * What do we do with the access token?
         * Check if the user exists
         * -> if they don't, generate a random password and then create and activate an account
         * manually log them in
         * redirect them to their dashboard
         */
        $details = \json_decode((string) $response->getBody(), true);

        $user_response = $this->httpClient->get($this->resourceUri, [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $details['access_token'],
            ],
        ]);
        $user_details = \json_decode((string) $user_response->getBody(), true);

        try {
            /** @var SentinelUser $user */
            $user = $this->accounts->findByLogin($user_details['email']);
        } catch (UserNotFoundException $e) {
            $this->accounts->create(
                $user_details['email'],
                \uniqid('opencfp', true),
                ['activated' => 1]
            );
            $this->accounts->activate($user_details['email']);
            $user = $this->accounts->findByLogin($user_details['email']);
        }

        $this->sentinel->login($user->getUser());
        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }
}
