<?php

namespace OpenCFP\Provider;

use OpenCFP\Http\Controller\BaseController;

class ControllerResolver extends \Silex\ControllerResolver
{
    /**
     * We're overriding this protected method to auto-inject the application container
     * into our controllers.
     *
     * @param string $controller
     *
     * @return array|mixed
     */
    protected function createController($controller)
    {
        if (\strpos($controller, '::') !== false) {
            $instance = parent::createController($controller);

            // Injects container from side rather than constructor.
            if ($instance[0] instanceof BaseController) {
                $instance[0]->setApplication($this->app);
            }

            return $instance;
        }

        if (\strpos($controller, ':') === false) {
            throw new \LogicException(\sprintf('Unable to parse the controller name "%s".', $controller));
        }

        list($service, $method) = \explode(':', $controller, 2);

        if (!isset($this->app[$service])) {
            throw new \InvalidArgumentException(\sprintf('Service "%s" does not exist.', $controller));
        }

        return [$this->app[$service], $method];
    }
}
