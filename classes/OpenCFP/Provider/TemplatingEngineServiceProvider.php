<?php namespace OpenCFP\Provider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\ServiceProviderInterface;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class TemplatingEngineServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app->register(new TwigServiceProvider(), [
            'twig.path' => $app->templatesPath(),
            'options' => [
                'debug' => !$app->isProduction()
            ]
        ]);

        $app->register(new UrlGeneratorServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app['twig']->addGlobal('site', $app->config('application'));

        $app->before(function (Request $request, Application $app) {
            $app['twig']->addGlobal('current_page', $request->getRequestUri());
        });
    }
}
