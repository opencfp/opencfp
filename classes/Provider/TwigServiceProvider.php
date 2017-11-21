<?php

namespace OpenCFP\Provider;

use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;
use OpenCFP\Application;
use OpenCFP\Http\View\TalkHelper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_SimpleFunction;

class TwigServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $c)
    {
        $c->register(new SilexTwigServiceProvider, [
            'twig.path'    => $this->app['path']->templatesPath(),
            'twig.options' => [
                'debug' => !$this->app['env']->isProduction(),
                'cache' => $this->app->config('cache.enabled') ? $this->app['path']->cacheTwigPath() : false,
            ],
        ]);

        $c->extend('twig', function (Twig_Environment $twig, Application $app) {
            if (!$app['env']->isProduction()) {
                $twig->addExtension(new Twig_Extension_Debug);
            }

            $twig->addFunction(new Twig_SimpleFunction('uploads', function ($path) {
                return '/uploads/' . $path;
            }));

            $twig->addFunction(new Twig_SimpleFunction('assets', function ($path) {
                return '/assets/' . $path;
            }));

            $twig->addGlobal('site', $app->config('application'));

            // Twig Markdown Extension
            $engine = new MarkdownEngine\MichelfMarkdownEngine();
            $twig->addExtension(new MarkdownExtension($engine));

            $twig->addExtension(new \Twig_Extensions_Extension_Text());

            $twig->addGlobal(
                'talkHelper',
                $this->app[TalkHelper::class]
            );

            return $twig;
        });
    }
}
