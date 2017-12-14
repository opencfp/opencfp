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

namespace OpenCFP\Infrastructure\Templating;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('uploads', function ($path) {
                //TODO: use Path downloadFrom function function
                return '/uploads/' . $path;
            }),
            new Twig_SimpleFunction('assets', function ($path) {
                return '/assets/' . $path;
            }),

            new Twig_SimpleFunction('active', function ($route) {
                return $this->urlGenerator->generate($route)
                    === $this->requestStack->getCurrentRequest()->getRequestUri();
            }),
        ];
    }
}
