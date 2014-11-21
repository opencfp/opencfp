<?php namespace OpenCFP\Provider;

use Silex\Application;
use Ciconia\Ciconia;
use Silex\ServiceProviderInterface;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;
use Aptoma\Twig\Extension\MarkdownExtension;
use Symfony\Component\HttpFoundation\Request;
use Ciconia\Extension\Gfm\WhiteSpaceExtension;
use Ciconia\Extension\Gfm\InlineStyleExtension;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Response;
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
                'cache' => $app->config('cache.enabled') ? $app->cacheTwigPath() : false
            ]
        ]);

        if ($app->isProduction()) {
            $app->error(function (\Exception $e, $code) use ($app) {
                switch ($code) {
                    case 401:
                        $message = $app['twig']->render('error/401.twig');
                        break;
                    case 403:
                        $message = $app['twig']->render('error/403.twig');
                        break;
                    case 404:
                        $message = $app['twig']->render('error/404.twig');
                        break;
                    default:
                        $message = $app['twig']->render('error/500.twig');
                }

                return new Response($message, $code);
            });
        }

        $app['twig']->addFunction(new Twig_SimpleFunction('uploads', function($path) {
            return '/uploads/' . $path;
        }));

        $app['twig']->addFunction(new Twig_SimpleFunction('assets', function($path) {
            return '/assets/' . $path;
        }));

        $app['twig']->addGlobal('site', $app->config('application'));

        // Twig Markdown Extension
        $markdown = new Ciconia();
        $markdown->addExtension(new InlineStyleExtension);
        $markdown->addExtension(new WhiteSpaceExtension);
        $engine = new CiconiaEngine($markdown);

        $app['twig']->addExtension(new MarkdownExtension($engine));
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->before(function (Request $request, Application $app) {
            $app['twig']->addGlobal('current_page', $request->getRequestUri());
        });

        if ($app['sentry']->check()) {
            $app['twig']->addGlobal('user', $app['sentry']->getUser());
            $app['twig']->addGlobal('user_is_admin', $app['sentry']->getUser()->hasAccess('admin'));
        }

        if ($app['session']->has('flash')) {
            $app['twig']->addGlobal('flash', $app['session']->get('flash'));
            $app['session']->set('flash', null);
        }
    }
}
