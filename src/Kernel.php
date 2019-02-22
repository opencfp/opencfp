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

namespace OpenCFP;

use Oneup\FlysystemBundle\OneupFlysystemBundle;
use OpenCFP\Test\Helper\DependencyInjection\TestingPass;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Yaml\Yaml;
use WouterJ\EloquentBundle\WouterJEloquentBundle;

final class Kernel extends SymfonyKernel
{
    public function __construct(string $environment, bool $debug)
    {
        $this->name = 'OpenCFP';

        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new MonologBundle(),
            new TwigBundle(),
            new SwiftmailerBundle(),
            new WouterJEloquentBundle(),
            new OneupFlysystemBundle(),
        ];

        if ($this->getEnvironment() !== Environment::TYPE_PRODUCTION) {
            $bundles[] = new DebugBundle();
        }

        if ($this->getEnvironment() === Environment::TYPE_DEVELOPMENT) {
            $bundles[] = new WebServerBundle();
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $this->bindConfiguration($container, $this->loadConfigurationFor($this->getEnvironment()));
        });

        $loader->load($this->getProjectDir() . '/resources/config/config_' . $this->getEnvironment() . '.yml');
    }

    public function getCacheDir()
    {
        return $this->getProjectDir() . '/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return $this->getProjectDir() . '/log';
    }

    protected function build(ContainerBuilder $container)
    {
        if ($this->getEnvironment() === Environment::TYPE_TESTING) {
            $container->addCompilerPass(new TestingPass());
        }
    }

    private function loadConfigurationFor(string $environment): array
    {
        $configFile = $this->getProjectDir() . '/config/' . $environment . '.yml';

        if (!\file_exists($configFile)) {
            throw new \RuntimeException(\sprintf(
                'The config file "%s" does not exist.',
                $configFile
            ));
        }

        return \array_merge(
            [
                'log'  => ['level' => 'DEBUG'],
                'talk' => [
                    'categories' => null,
                    'levels'     => null,
                    'types'      => null,
                ],
                'reviewer' => ['users' => []],
            ],
            Yaml::parse(\file_get_contents($configFile))
        );
    }

    private function bindConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('config.application', $config['application']);

        foreach ($config as $groupName => $groupConfig) {
            $this->bindGroupConfiguration($container, $groupName, $groupConfig);
        }
    }

    private function bindGroupConfiguration(ContainerBuilder $container, string $groupName, $groupConfig)
    {
        if (!\is_array($groupConfig)) {
            return;
        }

        foreach ($groupConfig as $key => $value) {
            $container->setParameter("$groupName.$key", $value);
        }
    }
}
