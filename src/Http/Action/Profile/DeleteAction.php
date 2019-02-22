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

use OpenCFP\Domain\Services;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class DeleteAction
{
    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Services\Authentication
     */
    private $authentication;

    public function __construct(
        Services\Authentication $authentication,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->urlGenerator   = $urlGenerator;
    }

    /**
     * @Template("user/delete.twig")
     */
    public function __invoke(HttpFoundation\Request $request)
    {
        if (!$this->authentication->isAuthenticated()) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }
    }
}
