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

final class ControllerResolver extends \Silex\ControllerResolver
{
    protected function instantiateController($class)
    {
        if (isset($this->app[$class])) {
            return $this->app[$class];
        }

        return parent::instantiateController($class);
    }
}
