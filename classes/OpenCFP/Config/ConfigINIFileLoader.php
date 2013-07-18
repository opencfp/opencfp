<?php
namespace OpenCFP\Config;

class ConfigINIFileLoader implements ConfigLoaderInterface
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function load()
    {
        if (!is_readable($this->filename)) {
            throw new \Exception('Config file does not exist or is not readable.');
        }

        if (!$return = parse_ini_file($this->filename, true)) {
            throw new \Exception('Config file is not parsable.');
        }

        return $return;
    }
}
