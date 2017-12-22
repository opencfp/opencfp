<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
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

final class LogInAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->twig           = $twig;
        $this->urlGenerator   = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $this->authentication->authenticate(
                $request->get('email'),
                $request->get('password')
            );
        } catch (Services\AuthenticationException $exception) {
            $flash = [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => $exception->getMessage(),
            ];

            $request->getSession()->set('flash', $flash);

            $content = $this->twig->render('security/login.twig', [
                'email' => $request->get('email'),
                'flash' => $flash,
            ]);

            return new HttpFoundation\Response(
                $content,
                HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        }

        $url = $this->urlGenerator->generate('dashboard');

        return new HttpFoundation\RedirectResponse($url);
    }
}
