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

use OpenCFP\Infrastructure\Event\ExceptionListener;
use OpenCFP\Provider\ApplicationServiceProvider;
use OpenCFP\Provider\CallForPapersProvider;
use OpenCFP\Provider\ControllerResolverServiceProvider;
use OpenCFP\Provider\Gateways\ConsoleGatewayProvider;
use OpenCFP\Provider\Gateways\WebGatewayProvider;
use OpenCFP\Provider\HtmlPurifierServiceProvider;
use OpenCFP\Provider\ImageProcessorProvider;
use OpenCFP\Provider\ResetEmailerServiceProvider;
use OpenCFP\Provider\SentinelServiceProvider;
use OpenCFP\Provider\SwiftMailerServiceProvider;
use OpenCFP\Provider\TalkFilterProvider;
use OpenCFP\Provider\TalkHandlerProvider;
use OpenCFP\Provider\TalkHelperProvider;
use OpenCFP\Provider\TalkRatingProvider;
use OpenCFP\Provider\TestingServiceProvider;
use OpenCFP\Provider\TwigServiceProvider;
use OpenCFP\Provider\YamlConfigDriver;
use Silex\Application as SilexApplication;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $this->register(new ConsoleGatewayProvider());

        // Services...
        $this->register(new SessionServiceProvider(), [
            'session.test' => $environment->isTesting(),
        ]);
        $this->register(new FormServiceProvider());
        $this->register(new CsrfServiceProvider());
        $this->register(new ControllerResolverServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new LocaleServiceProvider());
        $this->register(new TranslationServiceProvider());
        $this->register(new MonologServiceProvider(), [
            'monolog.logfile' => $this->config('log.path') ?: "{$basePath}/log/app.log",
            'monolog.name'    => 'opencfp',
            'monolog.level'   => \strtoupper(
                $this->config('log.level') ?: 'debug'
            ),
        ]);

        $this->register(new CallForPapersProvider());
        $this->register(new SentinelServiceProvider());
        $this->register(new TwigServiceProvider());
        $this->register(new HtmlPurifierServiceProvider());
        $this->register(new ImageProcessorProvider());
        $this->register(new ResetEmailerServiceProvider());
        $this->register(new SwiftMailerServiceProvider());
        $this->register(new TalkHandlerProvider());
        $this->register(new TalkHelperProvider());
        $this->register(new TalkRatingProvider());
        $this->register(new TalkFilterProvider());

        // Application Services...
        $this->register(new ApplicationServiceProvider());

        // Testing
        if ($environment->isTesting()) {
            $this->register(new TestingServiceProvider());
        }

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
        return ['config', 'uploadTo', 'downloadFrom', 'templates', 'public', 'assets', 'cache.twig', 'cache.purifier'];
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

    private function registerGlobalErrorHandler()
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this['dispatcher'];
        $eventDispatcher->addSubscriber(new ExceptionListener($this['twig']));
    }
}
