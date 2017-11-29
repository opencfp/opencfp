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

namespace OpenCFP;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Provider\ApplicationServiceProvider;
use OpenCFP\Provider\CallForPapersProvider;
use OpenCFP\Provider\ControllerResolverServiceProvider;
use OpenCFP\Provider\Gateways\WebGatewayProvider;
use OpenCFP\Provider\HtmlPurifierServiceProvider;
use OpenCFP\Provider\ImageProcessorProvider;
use OpenCFP\Provider\ResetEmailerServiceProvider;
use OpenCFP\Provider\SentinelServiceProvider;
use OpenCFP\Provider\TalkFilterProvider;
use OpenCFP\Provider\TalkHandlerProvider;
use OpenCFP\Provider\TalkHelperProvider;
use OpenCFP\Provider\TalkRatingProvider;
use OpenCFP\Provider\TwigServiceProvider;
use OpenCFP\Provider\YamlConfigDriver;
use Silex\Application as SilexApplication;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig_Environment;

class Application extends SilexApplication
{
    public function __construct(string $basePath, Environment $environment)
    {
        parent::__construct();

        $this['path']  = new Path($basePath, $environment);
        $this['env']   = $environment;
        $this['debug'] = true;

        $this->bindPathsInApplicationContainer();
        $this->bindConfiguration();

        if ($timezone = $this->config('application.date_timezone')) {
            \date_default_timezone_set($timezone);
        }

        // Register Gateways...
        $this->register(new WebGatewayProvider());

        // Services...
        $this->register(new SessionServiceProvider());
        $this->register(new FormServiceProvider());
        $this->register(new CsrfServiceProvider());
        $this->register(new ControllerResolverServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new LocaleServiceProvider());
        $this->register(new TranslationServiceProvider());
        $this->register(new MonologServiceProvider(), [
            'monolog.logfile' => $this->config('log.path') ?: "{$basePath}/log/app.log",
            'monolog.name'    => 'opencfp',
            'monlog.level'    => \strtoupper(
                $this->config('log.level') ?: 'debug'
            ),
        ]);
        $this->register(new SwiftmailerServiceProvider(), [
            'swiftmailer.options' => [
                'host'       => $this->config('mail.host'),
                'port'       => $this->config('mail.port'),
                'username'   => $this->config('mail.username'),
                'password'   => $this->config('mail.password'),
                'encryption' => $this->config('mail.encryption'),
                'auth_mode'  => $this->config('mail.auth_mode'),
            ],
        ]);

        $this->register(new CallForPapersProvider());
        $this->register(new SentinelServiceProvider());
        $this->register(new TwigServiceProvider($this));
        $this->register(new HtmlPurifierServiceProvider());
        $this->register(new ImageProcessorProvider());
        $this->register(new ResetEmailerServiceProvider());
        $this->register(new TalkHandlerProvider());
        $this->register(new TalkHelperProvider());
        $this->register(new TalkRatingProvider());
        $this->register(new TalkFilterProvider());

        // Application Services...
        $this->register(new ApplicationServiceProvider());

        $this->setUpDataBaseConnection();
        $this->registerGlobalErrorHandler();
    }

    /**
     * Puts various paths into the application container.
     */
    protected function bindPathsInApplicationContainer()
    {
        foreach ($this->getConfigSlugs() as $slug) {
            $this["paths.{$slug}"] = $this['path']->{$this->camelCaseFrom($slug) . 'Path'}();
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
        $parts = \explode('.', $slug);

        $parts = \array_map(function ($value) {
            return \ucfirst($value);
        }, $parts);

        $parts[0] = \strtolower($parts[0]);

        return \implode('', $parts);
    }

    /**
     * Loads configuration and puts application in debug-mode if not in production environment.
     */
    protected function bindConfiguration()
    {
        $config = $this['path']->configPath();
        $driver = new YamlConfigDriver();

        if (!\file_exists($config)) {
            throw new \InvalidArgumentException(
                \sprintf("The config file '%s' does not exist.", $config)
            );
        }

        if ($driver->supports($config)) {
            $this['config'] = $driver->load($config);
        }

        if (!$this['env']->isProduction()) {
            $this['debug'] = true;
        }
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $path the configuration key in dot-notation
     *
     * @return null|string the configuration value
     */
    public function config($path)
    {
        $cursor = $this['config'];

        foreach (\explode('.', $path) as $part) {
            if (!isset($cursor[$part])) {
                return null;
            }

            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    private function setUpDataBaseConnection()
    {
        $this[Capsule::class]->setAsGlobal();
        $this[Capsule::class]->bootEloquent();
    }

    private function registerGlobalErrorHandler()
    {
        $this->error(function (\Exception $e, Request $request, $code) {
            if (\in_array('application/json', $request->getAcceptableContentTypes())) {
                $headers = [];

                if ($e instanceof HttpExceptionInterface) {
                    $code = $e->getStatusCode();
                    $headers = $e->getHeaders();
                }

                return new JsonResponse([
                    'error' => $e->getMessage(),
                ], $code, $headers);
            }

            /* @var Twig_Environment $twig */
            $twig = $this['twig'];

            switch ($code) {
                case Response::HTTP_UNAUTHORIZED:
                    $message = $twig->render('error/401.twig');

                    break;
                case Response::HTTP_FORBIDDEN:
                    $message = $twig->render('error/403.twig');

                    break;
                case Response::HTTP_NOT_FOUND:
                    $message = $twig->render('error/404.twig');

                    break;
                default:
                    $message = $twig->render('error/500.twig');
            }

            return new Response($message, $code);
        });
    }
}
