<?php

namespace OpenCFP;

use Igorw\Silex\ChainConfigDriver;
use Igorw\Silex\JsonConfigDriver;
use Igorw\Silex\PhpConfigDriver;
use Igorw\Silex\TomlConfigDriver;
use League\OAuth2\Server\Exception\OAuthException;
use OpenCFP\Provider\ApplicationServiceProvider;
use OpenCFP\Provider\Gateways\ApiGatewayProvider;
use OpenCFP\Provider\Gateways\OAuthGatewayProvider;
use OpenCFP\Provider\Gateways\WebGatewayProvider;
use OpenCFP\Provider\ImageProcessorProvider;
use OpenCFP\Provider\ResetEmailerServiceProvider;
use OpenCFP\Provider\TwigServiceProvider;
use OpenCFP\Provider\YamlConfigDriver;
use Silex\Application as SilexApplication;
use Igorw\Silex\ConfigServiceProvider;
use OpenCFP\Provider\DatabaseServiceProvider;
use OpenCFP\Provider\HtmlPurifierServiceProvider;
use OpenCFP\Provider\SentryServiceProvider;
use OpenCFP\Provider\SpotServiceProvider;
use OpenCFP\Provider\ControllerResolverServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Application extends SilexApplication
{
    /**
     * @param array       $basePath
     * @param Environment $environment
     */
    public function __construct($basePath, Environment $environment)
    {
        parent::__construct();

        $this['path'] = $basePath;
        $this['env'] = $environment;

        $this->bindPathsInApplicationContainer();
        $this->bindConfiguration();

        // Register Gateways...
        $this->register(new WebGatewayProvider);
        $this->register(new ApiGatewayProvider);
        $this->register(new OAuthGatewayProvider);

        // Services...
        $this->register(new SessionServiceProvider);
        $this->register(new FormServiceProvider);
        $this->register(new UrlGeneratorServiceProvider);
        $this->register(new ControllerResolverServiceProvider);
        $this->register(new DatabaseServiceProvider);
        $this->register(new ValidatorServiceProvider);
        $this->register(new TranslationServiceProvider);
        $this->register(new SwiftmailerServiceProvider, [
            'swiftmailer.options' => [
                'host' => $this->config('mail.host'),
                'port' => $this->config('mail.port'),
                'username' => $this->config('mail.username'),
                'password' => $this->config('mail.password'),
                'encryption' => $this->config('mail.encryption'),
                'auth_mode' => $this->config('mail.auth_mode'),
            ],
        ]);

        $this->register(new SentryServiceProvider);
        $this->register(new TwigServiceProvider);
        $this->register(new HtmlPurifierServiceProvider);
        $this->register(new SpotServiceProvider);
        $this->register(new ImageProcessorProvider);
        $this->register(new ResetEmailerServiceProvider());

        // Application Services...
        $this->register(new ApplicationServiceProvider);

        $this->registerGlobalErrorHandler($this);
    }

    /**
     * Puts various paths into the application container.
     */
    protected function bindPathsInApplicationContainer()
    {
        foreach ($this->getConfigSlugs() as $slug) {
            $this["paths.{$slug}"] = $this->{$this->camelCaseFrom($slug) . 'Path'}();
        }
    }

    private function getConfigSlugs()
    {
        return ['config', 'upload', 'templates', 'public', 'assets', 'cache.twig', 'cache.purifier'];
    }

    /**
     * Converts dot-separated configuration slugs to camel-case for use in
     * method calls.
     *
     * @param $slug
     *
     * @return string
     */
    private function camelCaseFrom($slug)
    {
        $parts = explode('.', $slug);

        $parts = array_map(function ($value) {
            return ucfirst($value);
        }, $parts);

        $parts[0] = strtolower($parts[0]);

        return implode('', $parts);
    }

    /**
     * Loads configuration and puts application in debug-mode if not in production environment.
     */
    protected function bindConfiguration()
    {
        /**
         * Replace reference to `ChainConfigDriver` with `null` when PR below is merged.
         * Symfony's YAML package deprecated allowing a path to be passed to `parse` method.
         * This was causing our test-suite to fail. When this is merged, we can upgrade and
         * all will be well again.
         *
         * @see https://github.com/igorw/ConfigServiceProvider/pull/46
         */
        $this->register(new ConfigServiceProvider($this->configPath(), [], new ChainConfigDriver([
            new PhpConfigDriver(),
            new YamlConfigDriver(), // This is ours; in OpenCFP/Provider/YamlConfigDriver.
            new JsonConfigDriver(),
            new TomlConfigDriver(),
        ]), 'config'));

        if ( ! $this->isProduction()) {
            $this['debug'] = true;
        }
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $path the configuration key in dot-notation
     *
     * @return string|null the configuration value
     */
    public function config($path)
    {
        $cursor = $this['config'];

        foreach (explode('.', $path) as $part) {
            if ( ! isset($cursor[$part])) {
                return null;
            }

            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    /**
     * Get the base path for the application.
     * @return string
     */
    public function basePath()
    {
        return $this['path'];
    }

    /**
     * Get the configuration path.
     * @return string
     */
    public function configPath()
    {
        return $this->basePath() . "/config/{$this['env']}.yml";
    }

    /**
     * Get the uploads path.
     * @return string
     */
    public function uploadPath()
    {
        return $this->basePath() . "/web/uploads";
    }

    /**
     * Get the templates path.
     * @return string
     */
    public function templatesPath()
    {
        return $this->basePath() . "/templates";
    }

    /**
     * Get the public path.
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath() . "/web";
    }

    /**
     * Get the assets path.
     * @return string
     */
    public function assetsPath()
    {
        return $this->basePath() . "/web/assets";
    }

    /**
     * Get the Twig cache path.
     * @return string
     */
    public function cacheTwigPath()
    {
        return $this->basePath() . "/cache/twig";
    }

    /**
     * Get the HTML Purifier cache path.
     * @return string
     */
    public function cachePurifierPath()
    {
        return $this->basePath() . "/cache/htmlpurifier";
    }

    /**
     * Tells if application is in production environment.
     * @return boolean
     */
    public function isProduction()
    {
        return $this['env']->equals(Environment::production());
    }

    /**
     * Tells if application is in development environment.
     * @return boolean
     */
    public function isDevelopment()
    {
        return $this['env']->equals(Environment::development());
    }

    /**
     * Tells if application is in testing environment.
     * @return boolean
     */
    public function isTesting()
    {
        return $this['env']->equals(Environment::testing());
    }

    private function registerGlobalErrorHandler(Application $app)
    {
        $app->error(function (\Exception $e, $code) use ($app) {
            /** @var Request $request */
            $request = $app['request'];

            if (in_array('application/json', $request->getAcceptableContentTypes())) {
                $headers = [];

                if ($e instanceof HttpExceptionInterface) {
                    $code = $e->getStatusCode();
                    $headers = $e->getHeaders();
                }

                if ($e instanceof OAuthException) {
                    $code = $e->httpStatusCode;
                    $headers = $e->getHttpHeaders();
                }

                return new JsonResponse([
                    'error' => $e->getMessage()
                ], $code, $headers);
            }

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
}
