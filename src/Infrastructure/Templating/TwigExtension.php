<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2020 OpenCFP
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
use Twig\TwigFunction;

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
            new TwigFunction('uploads', function ($path) {
                return $this->path->uploadPath() . $path;
            }),
            new TwigFunction('thumbnail', function ($path) {
                // Add the "thumb" segment to the given path
                $tmpThumbnailFilenameParts = \explode('.', $path);
                \array_splice($tmpThumbnailFilenameParts, -1, 0, ['thumb']);
                $path = \implode('.', $tmpThumbnailFilenameParts);

                return $this->path->uploadPath() . $path;
            }),
            new TwigFunction('assets', function ($path) {
                return $this->path->assetsPath() . $path;
            }),

            new TwigFunction('active', function ($route) {
                return $this->urlGenerator->generate($route)
                    === $this->requestStack->getCurrentRequest()->getRequestUri();
            }),
        ];
    }
}
