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

use OpenCFP\Http\Controller\BaseController;

class ControllerResolver extends \Silex\ControllerResolver
{
    protected function instantiateController($class)
    {
        $controller = parent::instantiateController($class);

        if ($controller instanceof BaseController) {
            $controller->setApplication($this->app);
        }

        return $controller;
    }
}
