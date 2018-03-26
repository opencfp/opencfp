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
use GuzzleHttp\Client;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelUser;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

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
        $this->sentinel     = $sentinel;
        $this->accounts     = $accounts;
        $this->urlGenerator = $urlGenerator;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri  = $redirectUri;
        $this->resourceUri  = $resourceUri;
        $this->tokenUrl     = $tokenUrl;
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
        $http     = new Client();
        $response = $http->post($this->tokenUrl, [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'code'          => $request->get('code'),
                ],
            ]);

        /**
         * What do we do with the access token?
         * Check if the user exists
         * -> if they don't, generate a random password and then create and activate an account
         * manually log them in
         * redirect them to their dashboard
         */
        $details       = \json_decode((string) $response->getBody(), true);
        $user_response = $http->get($this->resourceUri, [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $details['access_token'],
            ],
        ]);
        $user_details = \json_decode((string) $user_response->getBody(), true);

        try {
            /** @var SentinelUser $user */
            $user = $this->accounts->findByLogin($user_details['email']);

            if ($user !== null) {
                $this->sentinel->login($user->getUser());
            }
        } catch (UserNotFoundException $e) {
            $this->accounts->create(
                $user_details['email'],
                \uniqid('opencfp', true),
                ['activated' => 1]
            );
            $this->accounts->activate($user_details['email']);
            $user = $this->accounts->findByLogin($user_details['email']);
            $user->save();
        }

        $this->sentinel->login($user->getUser());
        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }
}
