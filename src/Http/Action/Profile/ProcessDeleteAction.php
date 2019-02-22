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

namespace OpenCFP\Http\Action\Profile;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class ProcessDeleteAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->urlGenerator   = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $user = User::find($this->authentication->user()->getId());

        if (!$user) {
            $url = $this->urlGenerator->generate('login');

            return new HttpFoundation\RedirectResponse($url);
        }

        try {
            $user->delete();
            $this->authentication->logout();
            $url = $this->urlGenerator->generate('homepage');

            // Set flash message acknowledging that your account has been deleted
            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Account Deleted',
                'ext'   => 'Your OpenCFP account here has been deleted',
            ]);
        } catch (\Exception $e) {
            // Set flash message acknowledging that your account has been deleted
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Account Not Deleted',
                'ext'   => 'Your OpenCFP account could not be deleted. Please try again',
            ]);
            $url = $this->urlGenerator->generate('user_delete');
        }

        return new HttpFoundation\RedirectResponse($url);
    }
}
