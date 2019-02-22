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

namespace OpenCFP\Http\Action\Admin\Speaker;

use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class PromoteAction
{
    /**
     * @var Services\AccountManagement
     */
    private $accountManagement;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\AccountManagement $accountManagement,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->accountManagement = $accountManagement;
        $this->urlGenerator      = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $role = $request->get('role');
        $id   = (int) $request->get('id');

        try {
            $user = $this->accountManagement->findById($id);
        } catch (Auth\UserNotFoundException $exception) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \sprintf(
                    'User with id "%s" could not be found.',
                    $id
                ),
            ]);

            $url = $this->urlGenerator->generate('admin_speakers');

            return new HttpFoundation\RedirectResponse($url);
        }

        if ($user->hasAccess(\strtolower($role))) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \sprintf(
                    'User already is in the "%s" group.',
                    $role
                ),
            ]);

            $url = $this->urlGenerator->generate('admin_speakers');

            return new HttpFoundation\RedirectResponse($url);
        }

        try {
            $this->accountManagement->promoteTo(
                $user->getLogin(),
                $role
            );
        } catch (Auth\RoleNotFoundException $exception) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \sprintf(
                    'Role "%s" could not be found.',
                    $role
                ),
            ]);

            $url = $this->urlGenerator->generate('admin_speakers');

            return new HttpFoundation\RedirectResponse($url);
        }

        $request->getSession()->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => '',
        ]);

        $url = $this->urlGenerator->generate('admin_speakers');

        return new HttpFoundation\RedirectResponse($url);
    }
}
