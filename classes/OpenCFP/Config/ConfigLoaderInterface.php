<?php
namespace OpenCFP\Config;

interface ConfigLoaderInterface
{
    public function load($filename);
}
