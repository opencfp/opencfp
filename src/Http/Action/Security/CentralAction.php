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

use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class CentralAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /** @var int */
    private $clientId;

    /** @var string */
    private $redirectUri;

    /** @var string */
    private $authorizeUrl;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    /** @var string */
    private $sso;

    /**
     * LoginAction constructor.
     *
     * @param Services\Authentication                 $authentication
     * @param Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param int                                     $clientId
     * @param string                                  $redirectUri
     * @param string                                  $authorizeUrl
     */
    public function __construct(
        Services\Authentication $authentication,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        int $clientId,
        string $redirectUri,
        string $authorizeUrl
    ) {
        $this->authentication = $authentication;
        $this->urlGenerator   = $urlGenerator;
        $this->clientId       = $clientId;
        $this->redirectUri    = $redirectUri;
        $this->authorizeUrl   = $authorizeUrl;
    }

    /**
     * @param HttpFoundation\Request $request
     *
     * @throws \InvalidArgumentException
     *
     * @return HttpFoundation\Response
     */
    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        if ($this->authentication->isAuthenticated()) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $query = \http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => '',
        ]);

        return new HttpFoundation\RedirectResponse($this->authorizeUrl . $query);
    }
}
