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

namespace OpenCFP\Infrastructure\Templating;

use OpenCFP\PathInterface;
use OpenCFP\WebPath;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig_SimpleFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PathInterface
     */
    private $path;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator, WebPath $path)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->path         = $path;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('uploads', function ($path) {
                return $this->path->uploadPath() . $path;
            }),
            new Twig_SimpleFunction('assets', function ($path) {
                return  $this->path->assetsPath() . $path;
            }),

            new Twig_SimpleFunction('active', function ($route) {
                return $this->urlGenerator->generate($route)
                    === $this->requestStack->getCurrentRequest()->getRequestUri();
            }),
        ];
    }
}
