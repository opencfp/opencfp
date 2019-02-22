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

namespace OpenCFP\Http\Action;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class DashboardAction
{
    /**
     * @var Speakers
     */
    private $speakers;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Speakers $speakers,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->speakers     = $speakers;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Template("dashboard.twig")
     */
    public function __invoke()
    {
        try {
            return [
                'profile' => $this->speakers->findProfile(),
            ];
        } catch (Services\NotAuthenticatedException $exception) {
            $url = $this->urlGenerator->generate('login');

            return new HttpFoundation\RedirectResponse($url);
        }
    }
}
