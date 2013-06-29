<?php

namespace OpenCFP\Config;

class ConfigINIFileLoader implements ConfigLoaderInterface
{
    private $container;
    private $resolver;

    public function __construct(\Pimple $container, ParameterResolverInterface $resolver)
    {
        $this->container = $container;
        $this->resolver = $resolver;
    }

    public function load($filename)
    {
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not exist or is not readable.', $filename));
        }

        if (!$config = parse_ini_file($filename, true, INI_SCANNER_RAW)) {
            throw new \RuntimeException(sprintf('Unable parse configuration INI file: %s.', $filename));
        }

        array_walk_recursive($config, array(__CLASS__, 'parseValue'));
        foreach ($config as $category => $data) {
            foreach ($data as $key => $value) {
                $name = sprintf('%s.%s', $category, $key);
                $this->container[$name] = $this->resolver->resolve($value);
            }
        }
    }

    public static function parseValue(&$value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if ('null' === strtolower($value)) {
            $value = null;
        } else if (preg_match('/^(true|false)$/i', $value)) {
            $value = 'true' === strtolower($value);
        } else if (preg_match('/^-?\d+$/', $value)) {
            $value = (int) $value;
        } else if (is_numeric($value)) {
            $value = (float) $value;
        }

        return $value;
    }
}
