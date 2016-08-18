<?php namespace OpenCFP\Provider;

use Aptoma\Twig\Extension\MarkdownExtension;
use Ciconia\Ciconia;
use Ciconia\Extension\Gfm\InlineStyleExtension;
use Ciconia\Extension\Gfm\WhiteSpaceExtension;
use Silex\Application;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;
use Silex\ServiceProviderInterface;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_SimpleFunction;

class TwigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app->register(new SilexTwigServiceProvider(), [
            'twig.path' => $app->templatesPath(),
            'twig.options' => [
                'debug' => !$app->isProduction(),
                'cache' => $app->config('cache.enabled') ? $app->cacheTwigPath() : false,
            ],
        ]);

        /* @var Twig_Environment $twig */
        $twig = $app['twig'];

        $twig->addGlobal('current_page', function() use ($app) {
            return $app['request']->getRequestUri();
        });
        $twig->addGlobal('cfp_open', strtotime('now') < strtotime($app->config('application.enddate') . ' 11:59 PM'));

        if (!$app->isProduction()) {
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
        $markdown = new Ciconia();
        $markdown->addExtension(new InlineStyleExtension);
        $markdown->addExtension(new WhiteSpaceExtension);
        $engine = new CiconiaEngine($markdown);

        $twig->addExtension(new MarkdownExtension($engine));
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
