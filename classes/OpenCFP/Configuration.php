<?php
namespace OpenCFP;

class Configuration
{
    protected $config;

    public function __construct(ConfigLoaderInterface $loader)
    {
        $this->config = $loader->load();

        $valid_keys = array(
            'database' => array('dsn', 'user', 'password'),
            'application' => array('title', 'url'),
            'twig' => array('template_dir')
        );
        $this->validateKeys($this->config, $valid_keys);
    }

    public function getPDODSN()
    {
        return $this->config['database']['dsn'];
    }

    public function getPDOUser()
    {
        return $this->config['database']['user'];
    }

    public function getPDOPassword()
    {
        return $this->config['database']['password'];
    }

    public function getTwigTemplateDir()
    {
        return $this->config['twig']['template_dir'];
    }

    protected function validateKeys(array $keys, array $valid_keys, $path = '') {
        foreach ($valid_keys as $valid_key => $valid_value) {
            if (is_array($valid_value)) {
                if (!isset($keys[$valid_key])) {
                    throw new \Exception('Config file does not contain a ' . $path . '[' . $valid_key . '] section.');
                }

                $this->validateKeys($keys[$valid_key], $valid_value, $path . '[' . $valid_key . ']');
                continue;
            }

            if (!isset($keys[$valid_value])) {
                throw new \Exception('Config file does not contain a ' . $path . '[' . $valid_value . '] value.');
            }
        }
    }

}
