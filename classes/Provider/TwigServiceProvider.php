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

namespace OpenCFP\Provider;

use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;
use OpenCFP\Application;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\View\TalkHelper;
use OpenCFP\Infrastructure\Event\TwigGlobalsListener;
use OpenCFP\Infrastructure\Templating\TwigExtension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormRenderer;
use Twig_Environment;
use Twig_Extension_Debug;

final class TwigServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->register(new SilexTwigServiceProvider(), [
            'twig.path'    => $app['path']->templatesPath(),
            'twig.options' => [
                'debug' => !$app['env']->isProduction(),
                'cache' => $app['path']->cacheTwigPath(),
            ],
        ]);

        $app->extend('twig', function (Twig_Environment $twig, Application $app) {
            if (!$app['env']->isProduction()) {
                $twig->addExtension(new Twig_Extension_Debug());
            }

            $twig->addExtension(new TwigExtension(
                $app['request_stack'],
                $app['url_generator'],
                $app['path']
            ));

            $twig->addGlobal('site', $app->config('application'));

            // Twig Markdown Extension
            $engine = new MarkdownEngine\MichelfMarkdownEngine();
            $twig->addExtension(new MarkdownExtension($engine));

            $twig->addExtension(new \Twig_Extensions_Extension_Text());

            $twig->addGlobal(
                'talkHelper',
                $app[TalkHelper::class]
            );

            return $twig;
        });

        // Workaround for a Symfony 3.4 incompatibility.
        // See https://github.com/silexphp/Silex/pull/1571
        $app->extend('twig.runtimes', function (array $runtime) {
            $runtime[FormRenderer::class] = 'twig.form.renderer';

            return $runtime;
        });
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new TwigGlobalsListener(
            $app[Authentication::class],
            $app[CallForPapers::class],
            $app['session'],
            $app['twig']
        ));
    }
}
