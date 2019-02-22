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
use OpenCFP\Domain\ValidationException;
use OpenCFP\Infrastructure\Auth\UserNotFoundException;
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
            // Validate that the email address is a valid email before attempting to log in
            // This will prevent improperly-formed emails from potentially getting recognized
            // as a missing user, not invalid input.
            if (\filter_var($request->get('email'), FILTER_VALIDATE_EMAIL) === false) {
                throw ValidationException::withErrors(['The email address is improperly formatted.']);
            }

            $this->authentication->authenticate(
                $request->get('email'),
                $request->get('password')
            );

            $user = $this->authentication->user();
        } catch (UserNotFoundException $exception) {
            $flash = [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'User does not exist in the system; you can sign up below!',

                // Used to pre-populate the email field on the signup form
                'old_email' => $request->get('email'),
            ];

            $request->getSession()->set('flash', $flash);

            return new HttpFoundation\RedirectResponse($this->urlGenerator->generate('user_new'));
        } catch (ValidationException $exception) {
            $flash = [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br />', $exception->errors()),
            ];

            $request->getSession()->set('flash', $flash);

            $content = $this->twig->render('security/login.twig', [
                'flash' => $flash,
            ]);

            return new HttpFoundation\Response(
                $content,
                HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        } catch (Services\AuthenticationException | Services\NotAuthenticatedException $exception) {
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

        if ($user->hasAccess('admin')) {
            $url = $this->urlGenerator->generate('admin');
        } elseif ($user->hasAccess('reviewer')) {
            $url = $this->urlGenerator->generate('reviewer');
        } else {
            $url = $this->urlGenerator->generate('dashboard');
        }

        return new HttpFoundation\RedirectResponse($url);
    }
}
