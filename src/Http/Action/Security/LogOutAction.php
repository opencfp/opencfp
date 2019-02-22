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

final class LogOutAction
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

    public function __invoke(): HttpFoundation\Response
    {
        $this->authentication->logout();

        $url = $this->urlGenerator->generate('homepage');

        return new HttpFoundation\RedirectResponse($url);
    }
}
