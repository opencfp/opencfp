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

namespace OpenCFP\Http\Action\Opencfp;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Domain\Services\AccountManagement;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use GuzzleHttp\Client;

final class CallbackAction
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

    public function __construct(
        Sentinel $sentinel,
        AccountManagement $accounts,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        int $clientId,
        string $clientSecret,
        string $redirectUri,
        string $resourceUri,
        string $tokenUrl
    ) {
        $this->sentinel       = $sentinel;
        $this->accounts       = $accounts;
        $this->urlGenerator   = $urlGenerator;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->resourceUri = $resourceUri;
        $this->tokenUrl = $tokenUrl;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $http = new Client;
        $response = $http->post($this->tokenUrl, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $request->get('code')
                ],
            ]);

        /**
         * What do we do with the access token?
         * Check if the user exists
         * -> if they don't, generate a random password and then create and activate an account
         * manually log them in
         * redirect them to their dashboard
         */
        $details = json_decode((string) $response->getBody(), true);
        $user_response = $http->get($this->resourceUri, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $details['access_token']
            ]
        ]);
        $opencfp_central_user_details = json_decode((string) $user_response->getBody(), true);
        $opencfp_central_user = $this->accounts->findByLogin($opencfp_central_user_details['email']);

        if (!$opencfp_central_user) {
            die('Create an account!');
        }

        $this->sentinel->login($opencfp_central_user->getUser());
        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }
}
