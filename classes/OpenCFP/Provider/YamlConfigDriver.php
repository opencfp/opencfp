<?php

namespace OpenCFP\Provider;

use Igorw\Silex\YamlConfigDriver as IgorYamlConfigDriver;
use Symfony\Component\Yaml\Yaml;

class YamlConfigDriver extends IgorYamlConfigDriver
{
    public function load($filename)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }
        $config = Yaml::parse(file_get_contents($filename));
        return $config ?: array();
    }
}