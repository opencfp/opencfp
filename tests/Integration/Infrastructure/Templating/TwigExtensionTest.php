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

namespace OpenCFP\Test\Integration\Infrastructure\Templating;

use OpenCFP\Infrastructure\Templating\TwigExtension;
use OpenCFP\WebPath;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class TwigExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function extension()
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/dashboard'));

        $path = new WebPath();

        $routes = new RouteCollection();
        $routes->add('admin', new Route('/admin'));
        $routes->add('dashboard', new Route('/dashboard'));
        $urlGenerator = new UrlGenerator($routes, new RequestContext());

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . '/Fixtures'));
        $twig->addExtension(new TwigExtension(
            $requestStack,
            $urlGenerator,
            $path
        ));

        $this->assertStringEqualsFile(__DIR__ . '/Fixtures/functions.txt', $twig->render('functions.txt.twig'));
    }
}
