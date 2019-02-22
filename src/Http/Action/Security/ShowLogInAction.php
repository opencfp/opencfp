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

use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ShowLogInAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /** @var string */
    private $sso;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator,
        string $sso
    ) {
        $this->authentication = $authentication;
        $this->twig           = $twig;
        $this->urlGenerator   = $urlGenerator;
        $this->sso            = $sso;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        /**
         * If we're already logged in, redirect the user to the dashboard
         * Otherwise show the login page
         */
        try {
            $this->authentication->authenticate(
                $request->get('email'),
                $request->get('password')
            );
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        } catch (Services\AuthenticationException $exception) {
            $content = $this->twig->render('security/login.twig', ['sso' => $this->sso]);

            return new HttpFoundation\Response($content);
        }
    }
}
