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

namespace OpenCFP\Http\Action;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class DashboardAction
{
    /**
     * @var Speakers
     */
    private $speakers;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Speakers $speakers,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->speakers     = $speakers;
        $this->twig         = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(): HttpFoundation\Response
    {
        try {
            $content = $this->twig->render('dashboard.twig', [
                'profile' => $this->speakers->findProfile(),
            ]);

            return new HttpFoundation\Response($content);
        } catch (Services\NotAuthenticatedException $exception) {
            $url = $this->urlGenerator->generate('login');

            return new HttpFoundation\RedirectResponse($url);
        }
    }
}
