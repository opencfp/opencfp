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

namespace OpenCFP\Provider;

use Symfony\Component\Yaml\Yaml;

class YamlConfigDriver
{
    public function load($filename)
    {
        if (!\class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }
        $config = Yaml::parse(\file_get_contents($filename));

        return $config ?: [];
    }

    public function supports($filename)
    {
        return (bool) \preg_match('#\.ya?ml(\.dist)?$#', $filename);
    }
}
